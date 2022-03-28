<?php

namespace Gaara;

use Gaara\Exception\AuthenticateException;

/**
 * 登录凭证验证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface CredentialValidatorInterface
{
    /**
     * 验证登录凭证, 成功返回用户, 失败抛出异常
     *
     * @param UserProviderInterface $userProvider
     * @param array $credential
     * @return UserInterface
     * @throws AuthenticateException
     */
    function validateCredential(UserProviderInterface $userProvider, array $credential): UserInterface;
}
