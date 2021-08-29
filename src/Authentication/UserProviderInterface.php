<?php

namespace Gaara\Authentication;

use Gaara\User\UserInterface;

/**
 * 用户提供器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface UserProviderInterface
{
	/**
	 * 获取指定用户标识的用户, 找不到返回null
	 *
	 * @param mixed $id 用户标识
	 * @return UserInterface|null
	 */
	function findById($id): ?UserInterface;

	/**
	 * 获取符合指定参数的用户，没符合条件返回null
	 *
	 * @param array $params 参数
	 * @return UserInterface|null
	 */
	function findByParams(array $params): ?UserInterface;
}
