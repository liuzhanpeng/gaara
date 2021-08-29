<?php

namespace Gaara;

use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authentication\CredentialInterface;
use Gaara\Authentication\Credential\CallbackCrendential;
use Gaara\Authentication\Credential\GenericCrendential;
use Gaara\Authentication\Exception\InvalidCredentialException;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authorization\AuthorizatorInterface;
use Gaara\User\UserInterface;

class Gate
{
	/**
	 * 用户提供器
	 *
	 * @var UserProviderInterface
	 */
	protected $userProvider;

	/**
	 * 认证器
	 *
	 * @var AuthenticatorInterface
	 */
	protected $authenticator;

	/**
	 * 授权器
	 *
	 * @var AuthorizatorInterface
	 */
	protected $authorizator;

	/**
	 * 构造
	 *
	 * @param UserProviderInterface $userProvider
	 * @param AuthenticatorInterface $authenticator
	 * @param AuthorizatorInterface|null $authorizator
	 */
	public function __construct(
		UserProviderInterface $userProvider,
		AuthenticatorInterface $authenticator,
		?AuthorizatorInterface $authorizator = null
	) {
		$this->userProvider = $userProvider;
		$this->authenticator = $authenticator;
		$this->authorizator = $authorizator;
	}

	/**
	 * 登录
	 *
	 * @param CredentialInterface|array|callable $credential 登录证书
	 * @return mixed
	 */
	public function login($credential)
	{
		if (is_array($credential)) {
			$credential = new GenericCrendential($credential);
		} elseif (is_callable($credential)) {
			$credential = new CallbackCrendential($credential);
		}

		if (!$credential instanceof CredentialInterface) {
			throw new InvalidCredentialException('不支持的登录证书类型');
		}

		$user = $credential->validate($this->userProvider);

		return $this->authenticator->authenticate($user);
	}

	/**
	 * 直接认证指定用户身份
	 *
	 * @param UserInterface $user 用户身份
	 * @return mixed
	 */
	public function authenticate(UserInterface $user)
	{
		return $this->authenticator->authenticate($user);
	}

	/**
	 * 返回用户标识, 未认证返回null
	 *
	 * @return mixed
	 */
	public function id()
	{
		return $this->authenticator->id();
	}

	/**
	 * 返回用户身份, 未认证返回null
	 *
	 * @return UserInterface|null
	 */
	public function user(): ?UserInterface
	{
		return $this->authenticator->user($this->userProvider);
	}

	/**
	 * 是否已认证
	 *
	 * @return boolean
	 */
	public function isAuthenticated(): bool
	{
		return $this->authenticator->isAuthenticated();
	}

	/**
	 * 注销
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->authenticator->clearUser();
	}

	/**
	 * 是否可访问指定资源
	 *
	 * @param mixed $resourceId
	 * @return boolean
	 */
	public function isAllowed($resourceId): bool
	{
		if (!$this->isAuthenticated()) {
			return false;
		}

		if (!is_null($this->authorizator)) {
			throw new \Exception('未设置授权器');
		}

		return $this->authorizator->isAllowed($this->user(), $resourceId);
	}
}
