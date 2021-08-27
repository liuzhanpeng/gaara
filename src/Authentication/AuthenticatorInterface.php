<?php

namespace Gaara\Authentication;

use Gaara\User\UserInterface;

/**
 * 认证器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface AuthenticatorInterface
{
	/**
	 * 认证上下文的用户身份
	 * 成功返回令牌，否则抛出异常
	 *
	 * @param UserInterface $user 用户身份
	 * @return TokenInterface
	 */
	function authenticate(UserInterface $user): TokenInterface;

	/**
	 * 通过上下文获取用户身份
	 * 找不到返回null
	 *
	 * @return UserInterface|null
	 */
	function user(): ?UserInterface;

	/**
	 * 清除上下文的用户身份
	 *
	 * @return void
	 */
	function clearUser();
}
