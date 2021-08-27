<?php

namespace Gaara\Authentication\Token;

use Gaara\Authentication\TokenInterface;
use Gaara\User\UserInterface;

class NullToken implements TokenInterface
{
	protected $user;

	public function __construct(UserInterface $user)
	{
		$this->user = $user;
	}

	public function user(): UserInterface
	{
		return $this->user;
	}

	public function toString(): string
	{
		return '';
	}
}
