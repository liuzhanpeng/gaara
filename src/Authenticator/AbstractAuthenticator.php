<?php

namespace Gaara\Authenticator;

use Gaara\AuthenticatorInterface;
use Gaara\CredentialValidatorInterface;
use Gaara\Identity;
use Gaara\UserInterface;
use Gaara\UserProviderInterface;

/**
 * 认证器抽象类
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
abstract class AbstractAuthenticator implements AuthenticatorInterface
{
    /**
     * 用户提供器
     *
     * @var UserProviderInterface
     */
    protected UserProviderInterface $userProvider;

    /**
     * 登录凭证验证器
     *
     * @var CredentialValidatorInterface
     */
    protected CredentialValidatorInterface $credentialValidator;

    /**
     * @inheritDoc
     */
    public function setUserProvider(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @inheritDoc
     */
    public function setCredentialValidator(CredentialValidatorInterface $credentialValidator)
    {
        $this->credentialValidator = $credentialValidator;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(array $credential): Identity
    {
        $user = $this->credentialValidator->validateCredential($this->userProvider, $credential);

        return $this->setUser($user);
    }

    /**
     * @inheritDoc
     */
    abstract function setUser(UserInterface $user): Identity;

    /**
     * @inheritDoc
     */
    abstract function isAuthenticated(): bool;

    /**
     * @inheritDoc
     */
    abstract function user(): ?UserInterface;

    /**
     * @inheritDoc
     */
    abstract function clearUser();
}
