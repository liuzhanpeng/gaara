<?php

namespace Gaara\CredentialValidator;

use Gaara\UserInterface;

/**
 * 含密码的用户接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface PasswordUserInterface extends UserInterface
{
    /**
     * 返回密码
     *
     * @return string
     */
    function password(): string;
}
