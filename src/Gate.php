<?php

namespace Gaara;

use Gaara\Authentication\AuthenticateResult;
use Gaara\Authentication\AuthenticatorInterface;
use Gaara\Authentication\CredentialValidatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authorization\AuthorizatorInterface;
use Gaara\User\UserInterface;
use Gaara\Authentication\Exception\AuthenticationException;

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
	 * 登录凭证验证器
	 *
	 * @var CredentialValidatorInterface
	 */
	protected $credentialValidator;

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
	 * @param CredentialValidatorInterface $credentialValidator
	 * @param AuthorizatorInterface|null $authorizator
	 */
	public function __construct(
		UserProviderInterface $userProvider,
		AuthenticatorInterface $authenticator,
		CredentialValidatorInterface $credentialValidator,
		?AuthorizatorInterface $authorizator = null
	) {
		$this->userProvider = $userProvider;
		$this->authenticator = $authenticator;
		$this->credentialValidator = $credentialValidator;
		$this->authorizator = $authorizator;
	}

	/**
	 * 登录
	 *
	 * @param array|callable $credential 登录凭证
	 * @return AuthenticateResult
	 * @throws AuthenticationException
	 */
	public function login($credential): AuthenticateResult
	{
		$user = $this->credentialValidator->validate($credential, $this->userProvider);

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
	 * @return mixed
	 */
	public function user()
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
	 * @throws \Exception
	 */
	public function isAllowed($resourceId): bool
	{
		if (!$this->isAuthenticated()) {
			return false;
		}

		if (is_null($this->authorizator)) {
			throw new \Exception('未设置授权器');
		}

		return $this->authorizator->isAllowed($this->user(), $resourceId);
	}
}
