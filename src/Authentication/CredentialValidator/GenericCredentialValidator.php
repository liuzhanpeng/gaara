<?php

namespace Gaara\Authentication\CredentialValidator;

use Gaara\Authentication\CredentialValidatorInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authentication\Exception\InvalidCredentialException;
use Gaara\User\UserInterface;

/**
 * 通用的登录凭证验证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class GenericCredentialValidator implements CredentialValidatorInterface
{
	/**
	 * @inheritDoc
	 */
	public function validate($credential, UserProviderInterface $userProvider): UserInterface
	{
		if (!is_array($credential)) {
			throw new InvalidCredentialException('登录凭证必须是数组类型');
		}

		$user = $userProvider->findByParams($credential);

		if (is_null($user)) {
			throw new InvalidCredentialException('无效登录凭证');
		}

		return $user;
	}
}
