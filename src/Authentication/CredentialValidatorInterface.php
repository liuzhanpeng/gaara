<?php

namespace Gaara\Authentication;

use Gaara\User\UserInterface;
use Gaara\Authentication\Exception\AuthenticationException;
use Gaara\Authentication\UserProviderInterface;

/**
 * 登录凭证验证器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface CredentialValidatorInterface
{
	/**
	 * 验证凭证是否合法, 合法返回用户身份，否则抛出异常
	 *
	 * @param array|callable $credential 登录凭证
	 * @param UserProviderInterface $userProvider
	 * @return UserInterface
	 * @throws AuthenticationException
	 */
	function validate($credential, UserProviderInterface $userProvider): UserInterface;
}
