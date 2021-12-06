<?php

namespace Gaara\Authentication\Authenticator;

use Gaara\Authentication\AuthenticateResult;
use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\User\UserInterface;

/**
 * 基于Session的认证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class SessionAuthenticator implements AuthenticatorInterface
{
	/**
	 * 会话中只保存用户标识
	 */
	const MODE_ONLY_ID = 0;

	/**
	 * 会话信息全部保存在session中
	 */
	const MODE_ALL_DATA = 1;

	/**
	 * 会话key
	 *
	 * @var string
	 */
	protected $sessionKey;

	/**
	 * Session实例
	 *
	 * @var SessionInterface
	 */
	protected $session;

	/**
	 * session保存模式
	 *
	 * @var integer
	 */
	protected $mode = self::MODE_ONLY_ID;

	/**
	 * 构造
	 *
	 * @param string $sessionKey 会话key
	 * @param SessionInterface $session 会话存储
	 * @param integer $mode 存储模式
	 */
	public function __construct(string $sessionKey, SessionInterface $session, $mode = self::MODE_ONLY_ID)
	{
		$this->sessionKey = $sessionKey;
		$this->session = $session;
		$this->mode = $mode;
	}

	/**
	 * @inheritDoc
	 */
	public function authenticate(UserInterface $user): AuthenticateResult
	{
		if ($this->mode === self::MODE_ALL_DATA) {
			$this->session->set($this->sessionKey, $user);
		} else {
			$this->session->set($this->sessionKey, $user->id());
		}

		return new AuthenticateResult($user);
	}

	/**
	 * @inheritDoc
	 */
	public function isAuthenticated(): bool
	{
		return $this->session->has($this->sessionKey);
	}

	/**
	 * 返回用户标识
	 *
	 * @return mixed
	 */
	public function id()
	{
		if ($this->mode === self::MODE_ALL_DATA) {
			$user = $this->session->get($this->sessionKey);
			return $user->id();
		}

		return $this->session->get($this->sessionKey);
	}

	/**
	 * @inheritDoc
	 */
	public function user(UserProviderInterface $userProvider): ?UserInterface
	{
		if (!$this->isAuthenticated()) {
			return null;
		}

		if ($this->mode === self::MODE_ALL_DATA) {
			return $this->session->get($this->sessionKey);
		}

		$id = $this->session->get($this->sessionKey);

		return $userProvider->findById($id);
	}

	/**
	 * @inheritDoc
	 */
	public function clearUser()
	{
		$this->session->delete($this->sessionKey);
	}
}
