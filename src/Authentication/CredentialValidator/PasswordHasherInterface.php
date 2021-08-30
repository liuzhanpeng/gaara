<?php

namespace Gaara\Authentication\CredentialValidator;

/**
 * 密码哈希接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface PasswordHasherInterface
{
	/**
	 * 哈希密码
	 *
	 * @param string $password
	 * @return string
	 */
	function hash(string $password): string;

	/**
	 * 验证密码
	 *
	 * @param string $hash
	 * @param string $password
	 * @return boolean
	 */
	function verify(string $hash, string $password): bool;
}
