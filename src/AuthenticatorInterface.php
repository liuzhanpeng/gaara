<?php

namespace Gaara;

use C;
use Gaara\Exception\AuthenticateException;

/**
 * 认证器接口 
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface AuthenticatorInterface
{
    /**
     * 设置用户提供器
     *
     * @param UserProviderInterface $userProvider
     * @return void
     */
    function setUserProvider(UserProviderInterface $userProvider);

    /**
     * 设置登录凭证验证器
     *
     * @param CredentialValidatorInterface $credentialValidator
     * @return void
     */
    function setCredentialValidator(CredentialValidatorInterface $credentialValidator);

    /**
     * 认证登录凭证
     *
     * @param array $credential
     * @return Identity
     * @throws AuthenticateException
     */
    function authenticate(array $credential): Identity;

    /**
     * 设置用户
     *
     * @param UserInterface $user
     * @return Identity
     */
    function setUser(UserInterface $user): Identity;

    /**
     * 判断当前用户是否已认证
     *
     * @return boolean
     */
    function isAuthenticated(): bool;

    /**
     * 返回当前用户身份
     *
     * @return UserInterface|null
     */
    function user(): ?UserInterface;

    /**
     * 消除当前用户的认证结果信息
     *
     * @return void
     */
    function clearUser();
}
