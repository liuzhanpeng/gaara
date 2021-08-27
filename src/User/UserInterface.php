<?php

namespace Gaara\User;

/**
 * 用户身份接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface UserInterface
{
	/**
	 * 返回用户唯一标识
	 *
	 * @return mixed
	 */
	function id();

	/**
	 * 返回用户数据
	 *
	 * @return array|\ArrayAccess
	 */
	function data();
}
