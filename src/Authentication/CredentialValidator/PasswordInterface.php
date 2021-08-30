<?php

namespace Gaara\Authentication\CredentialValidator;

/**
 * 用户密码接口
 * 
 * @inheritDoc
 */
interface PasswordInterface
{
	/**
	 * 返回密码
	 *
	 * @return string
	 */
	function password(): string;
}
