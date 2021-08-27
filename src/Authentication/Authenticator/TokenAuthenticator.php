<?php

namespace Gaara\Authentication\Authenticator;

use Gaara\Authentication\AuthenticatorInterface;
use Gaara\User\UserInterface;
use Gaara\User\UserProviderInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * 基于Token的认证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class TokenAuthenticator implements AuthenticatorInterface
{
	/**
	 * 令牌key
	 *
	 * @var string
	 */
	protected $tokenKey;

	/**
	 * 加密盐
	 *
	 * @var string
	 */
	protected $salt;

	/**
	 * 令牌超时时间
	 *
	 * @var integer
	 */
	protected $timeout;

	/**
	 * 缓存实例
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * 构造
	 *
	 * @param string $tokenKey 令牌key
	 * @param string $salt 加密盐
	 * @param integer $timeout 令牌超时时间
	 * @param CacheInterface $cache 缓存实例
	 */
	public function __construct(string $tokenKey, string $salt, int $timeout, CacheInterface $cache)
	{
		$this->tokenKey = $tokenKey;
		$this->salt = $salt;
		$this->timeout = $timeout;
		$this->cache = $cache;
	}

	/**
	 * @inheritDoc
	 */
	public function authenticate(UserInterface $user): ?string
	{
		$package = $this->generateTokenPackage($user);

		$this->cache->set($this->getCacheKey($user->id()), $package['token'], $this->timeout);

		return base64_encode(json_encode($package));
	}

	/**
	 * @inheritDoc
	 */
	public function isAuthenticated(): bool
	{
		$package = $this->parseToken($this->getToken());
		if ($package === false) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function id()
	{
		$package = $this->parseToken($this->getToken());
		if ($package === false) {
			return null;
		}

		return $package['userId'];
	}

	/**
	 * @inheritDoc
	 */
	public function user(UserProviderInterface $userProvider): ?UserInterface
	{
		$package = $this->parseToken($this->getToken());
		if ($package === false) {
			return null;
		}

		// 更新过期时间
		$this->cache->set($this->getCacheKey($package['userId']), $package['token'], $this->timeout);

		return $userProvider->findById($package['userId']);
	}

	/**
	 * @inheritDoc
	 */
	public function clearUser()
	{
		$package = $this->parseToken($this->getToken());
		if ($package === false) {
			return;
		}

		$this->cache->delete($this->getCacheKey($package['userId']));
	}

	/**
	 * 生成Token信息包
	 *
	 * @param UserInterface $user 用户身份
	 * @return array
	 */
	protected function generateTokenPackage(UserInterface $user): array
	{
		$timestamp = time();
		$randomStr = bin2hex(random_bytes(16));
		$orignStr = sprintf('%s-%s-%s', $user->id(), $timestamp, $randomStr);

		return [
			'userId' => $user->id(),
			'timestamp' => $timestamp,
			'randomStr' => $randomStr,
			'token' => hash_hmac('md5', $orignStr, $this->salt),
		];
	}

	/**
	 * 解析令牌返回相应信息
	 *
	 * @return array|false
	 */
	protected function parseToken(string $token)
	{
		$jsonStr = base64_decode($token);
		if ($jsonStr === false) {
			return false;
		}

		$package = json_decode($jsonStr, true);
		if (is_null($package)) {
			return false;
		}

		if (!isset($package['userId']) || !isset($package['value']) || !isset($package['timestamp']) || !isset($package['signature'])) {
			return false;
		}

		$orignStr = sprintf('%s-%s-%s', $package['userId'], $package['value'], $package['timestamp']);
		if (strcmp(hash_hmac('md5', $orignStr, $this->salt), $package['signature']) !== 0) {
			return false;
		}

		$token = $this->cache->get($this->getCacheKey($package['userId']));
		if (empty($token)) {
			return false;
		}

		if (strcmp($token, $package['token']) !== 0) {
			return false;
		}

		return $package;
	}

	/**
	 * 从上下文中获取Token信息
	 *
	 * @return string
	 */
	protected function getToken(): string
	{
		return $_SERVER[$this->tokenKey];
	}

	/**
	 * 返回缓存key
	 *
	 * @param mixed $userId
	 * @return string
	 */
	private function getCacheKey($userId): string
	{
		return $this->tokenKey . '-' . $userId;
	}
}
