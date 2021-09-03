<?php

namespace Gaara\Authentication;

use Gaara\User\UserInterface;

/**
 * 认证结果
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class AuthenticateResult
{
	protected $user;
	protected $data;

	public function __construct(UserInterface $user, array $data = [])
	{
		$this->user = $user;
		$this->data = $data;
	}

	/**
	 * 返回身份
	 *
	 * @return mixed
	 */
	public function user()
	{
		return $this->user;
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __get($name)
	{
		return $this->data[$name];
	}

	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	public function __unset($name)
	{
		unset($this->data[$name]);
	}
}
