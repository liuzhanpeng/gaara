<?php

namespace Gaara\Authorization\Authorizator;

use Gaara\Authentication\AuthenticateResult;
use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\User\UserInterface;
use Psr\SimpleCache\CacheInterface;

class OnceTokenAuthenticator implements AuthenticatorInterface
{
	/**
	 * 令牌key
	 *
	 * @var string
	 */
	protected $tokenKey;

	/**
	 * 令牌过期时间
	 *
	 * @var integer
	 */
	protected $expire;

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
	 * @param integer $expire 令牌过期时间
	 * @param CacheInterface $cache 缓存实例
	 */
	public function __construct(string $tokenKey, int $expire, CacheInterface $cache)
	{
		$this->tokenKey = $tokenKey;
		$this->expire = $expire;
		$this->cache = $cache;
	}

	/**
	 * @inheritDoc
	 */
	public function authenticate(UserInterface $user): AuthenticateResult
	{
		$token = $this->generateToken($user);

		$this->cache->set($token, $this->getCacheKey($user->id()), $this->expire);

		return new AuthenticateResult($user, [
			'token' => base64_encode($token)
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function isAuthenticated(): bool
	{
		$token = $this->getToken();
		if (empty($token)) {
			return false;
		}

		return $this->cache->has($token);
	}

	/**
	 * @inheritDoc
	 */
	public function id()
	{
		$token = $this->getToken();
		if (empty($token)) {
			return null;
		}

		return $this->cache->get($token);
	}

	/**
	 * @inheritDoc
	 */
	public function user(UserProviderInterface $userProvider): ?UserInterface
	{
		$token = $this->getToken();
		if (empty($token)) {
			return null;
		}

		if (!$this->cache->has($token)) {
			return null;
		}

		$id = $this->cache->get($token);

		// 用完即弃
		$this->cache->delete($token);

		return $userProvider->findById($id);
	}

	/**
	 * @inheritDoc
	 */
	public function clearUser()
	{
		$token = $this->getToken();
		if (empty($token)) {
			return;
		}

		$this->cache->delete($token);
	}

	/**
	 * 生成令牌
	 *
	 * @param UserInterface $user
	 * @return string
	 */
	protected function generateToken(UserInterface $user): string
	{
		return bin2hex(random_bytes(64));
	}

	/**
	 * 获取令牌
	 *
	 * @return string
	 */
	protected function getToken(): string
	{
		return $_GET[$this->tokenKey];
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
