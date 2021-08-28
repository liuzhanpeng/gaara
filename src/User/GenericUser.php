<?php

namespace Gaara\User;

/**
 * 通用的用户身份
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class GenericUser implements UserInterface, \ArrayAccess
{
	/**
	 * 用户标识
	 *
	 * @var mixed
	 */
	protected $id;

	/**
	 * 用户数据
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * 构造
	 *
	 * @param mixed $id 用户标识
	 * @param array $data 用户数据
	 */
	public function __construct($id, array $data = [])
	{
		$this->id = $id;
		$this->data = $data;
	}

	/**
	 * @inheritDoc
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function data()
	{
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
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
