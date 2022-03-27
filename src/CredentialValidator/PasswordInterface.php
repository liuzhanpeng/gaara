<?php

namespace Gaara\CredentialValidator;

/**
 * 用户密码接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
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
