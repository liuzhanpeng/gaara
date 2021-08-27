<?php

namespace Gaara\Authorization;

use Gaara\User\UserInterface;

/**
 * 授权器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface AuthorizatorInterface
{
	/**
	 * 判断指定用户身份是否可访问指定资源
	 *
	 * @param UserInterface $user 用户身份
	 * @param mixed $resourceId 资源标识
	 * @return boolean
	 */
	function isAllowed(UserInterface $user, $resourceId): bool;
}
