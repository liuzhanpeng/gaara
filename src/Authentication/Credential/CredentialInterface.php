<?php

namespace Gaara\Authentication\Credential;

use Gaara\User\UserProviderInterface;
use Gaara\User\UserInterface;
use Gaara\Authentication\Exception\AuthenticationException;

/**
 * 登录凭证接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface CredentialInterface
{
	/**
	 * 验证是否合法
	 *
	 * @param UserProviderInterface $userProvider
	 * @return UserInterface
	 * @throws AuthenticationException
	 */
	function validate(UserProviderInterface $userProvider): UserInterface;
}
