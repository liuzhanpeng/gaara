<?php

namespace Gaara\Authentication;

use Gaara\User\UserInterface;

/**
 * 用户令牌接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface TokenInterface
{
	/**
	 * 返回用户身份
	 *
	 * @return UserInterface
	 */
	function user(): UserInterface;

	/**
	 * 输出为字符串
	 *
	 * @return string
	 */
	function toString(): string;
}
