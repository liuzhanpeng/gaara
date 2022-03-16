<?php

namespace Gaara\Authentication\CredentialValidator;

use Gaara\Authentication\CredentialValidatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authentication\Exception\InvalidCredentialException;
use Gaara\User\UserInterface;

/**
 * 回调登录凭证验证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class CallbackCredentialValidator implements CredentialValidatorInterface
{
	/**
	 * @inheritDoc
	 */
	public function validate($credential, UserProviderInterface $userProvider): UserInterface
	{
		if (!is_callable($credential)) {
			throw new InvalidCredentialException('登录凭证必须是可调用类型');
		}

		$user = call_user_func($credential, $userProvider);

		if (is_null($user)) {
			throw new InvalidCredentialException('无效登录凭证');
		}
		if (!$user instanceof UserInterface) {
			throw new InvalidCredentialException('登录凭证调用必须返回UserInterface');
		}

		return $user;
	}
}
