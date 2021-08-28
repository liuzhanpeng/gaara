<?php

namespace Gaara\Authentication\Credential;

use Gaara\Authentication\CredentialInterface;
use Gaara\Authentication\Exception\InvalidCredentialException;
use Gaara\User\UserProviderInterface;
use Gaara\User\UserInterface;

/**
 * 回调登录凭证
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class CallbackCendential implements CredentialInterface
{
	protected $callback;

	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @inheritDoc
	 */
	public function validate(UserProviderInterface $userProvider): UserInterface
	{
		$user = call_user_func($this->callback, $userProvider);

		if (is_null($user)) {
			throw new InvalidCredentialException('无效登录凭证');
		}

		return $user;
	}
}
