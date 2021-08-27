<?php

namespace Gaara\Authentication\Authenticator;

/**
 * 会话接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface SessionInterface
{
	/**
	 * 设置指定key的会话信息
	 *
	 * @param string $key key
	 * @param mixed $data 数据
	 * @return void
	 */
	function set(string $key, $data);

	/**
	 * 判断指定key会话信息是否存在
	 *
	 * @param string $key key
	 * @return boolean
	 */
	function has(string $key): bool;

	/**
	 * 返回指定key会话信息
	 *
	 * @param string $key key
	 * @return mixed
	 */
	function get(string $key);

	/**
	 * 删除指定key会话信息
	 *
	 * @param string $key key
	 * @return void
	 */
	function delete(string $key);
}
