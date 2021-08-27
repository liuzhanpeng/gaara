<?php

namespace Gaara\Authorization;

use Gaara\User\UserInterface;

/**
 * 资源提供器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface ResourceProviderInterface
{
	/**
	 * 返回指定用户身份的资源列表
	 *
	 * @param UserInterface $user 用户身份
	 * @return ResourceInterface[]
	 */
	function resources(UserInterface $user): array;
}
