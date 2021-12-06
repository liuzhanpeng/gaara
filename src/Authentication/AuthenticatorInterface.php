<?php

namespace Gaara\Authentication;

use Gaara\User\UserInterface;
use Gaara\Authentication\UserProviderInterface;
use Gaara\Authentication\Exception\AuthenticationException;

/**
 * 认证器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface AuthenticatorInterface
{
	/**
	 * 认证上下文的用户身份
	 * 成功返回认证结果，否则抛出异常
	 *
	 * @param UserInterface $user 用户身份
	 * @return AuthenticateResult
	 * @throws AuthenticationException
	 */
	function authenticate(UserInterface $user): AuthenticateResult;

	/**
	 * 判断是否已认证
	 *
	 * @return boolean
	 */
	function isAuthenticated(): bool;

	/**
	 * 返回用户标识
	 *
	 * @return mixed
	 */
	function id();

	/**
	 * 通过上下文获取用户身份
	 * 找不到返回null
	 *
	 * @param UserProviderInterface $userProvider 用户提供器
	 * @return UserInterface|null
	 */
	function user(UserProviderInterface $userProvider): ?UserInterface;

	/**
	 * 清除上下文的用户身份
	 *
	 * @return void
	 */
	function clearUser();
}
