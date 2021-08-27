<?php

namespace Gaara;

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

	protected $authenticator;

	protected $authorizator;

	public function __construct(
		UserProviderInterface $userProvider
	) {
		$this->userProvider = $userProvider;
	}

	public function login()
	{
	}

	public function setUser(UserInterface $user)
	{
	}

	public function user(): ?UserInterface
	{
		return null;
	}

	public function isAuthenticated(): bool
	{
		return false;
	}

	public function isAllowed(): bool
	{
		return false;
	}

	public function logout()
	{
	}
}
