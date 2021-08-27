<?php

namespace Gaara;

use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authorization\AuthorizatorInterface;
use Gaara\User\UserProviderInterface;
use Gaara\User\UserInterface;

class Gate
{
	/**
	 * 用户身份提供器
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

	public function __construct(
		UserProviderInterface $userProvider,
		AuthenticatorInterface $authenticator,
		AuthorizatorInterface $authorizator
	) {
		$this->userProvider = $userProvider;
		$this->authenticator = $authenticator;
		$this->authorizator = $authorizator;
	}

	public function login()
	{
	}

	/**
	 * 直接认证指定用户身份
	 *
	 * @param UserInterface $user 用户身份
	 * @return string|null
	 */
	public function authenticate(UserInterface $user): ?string
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

		return $this->authorizator->isAllowed($this->user(), $resourceId);
	}
}
