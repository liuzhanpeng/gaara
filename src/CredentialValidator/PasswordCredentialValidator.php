<?php

namespace Gaara\CredentialValidator;

use Gaara\CredentialValidatorInterface;
use Gaara\Exception\AuthenticateException;
use Gaara\Exception\InvalidCredentialException;
use Gaara\UserInterface;
use Gaara\UserProviderInterface;

/**
 * 含密码的登录凭证验证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class PasswordCrdentialValidator implements CredentialValidatorInterface
{
    protected string $passwordField;

    public function __construct(string $passwordField)
    {
        $this->passwordField = $passwordField;
    }

    /**
     * @inheritDoc
     */
    public function validateCredential(UserProviderInterface $userProvider, array $credential): UserInterface
    {
        if (!isset($credential[$this->passwordField])) {
            throw new InvalidCredentialException('登录凭证未含有密码');
        }

        $password = $credential[$this->passwordField];
        unset($credential[$this->passwordField]);

        $user = $userProvider->findByCredential($credential);
        if (is_null($user)) {
            throw new InvalidCredentialException('无效登录凭证');
        }

        if (!$user instanceof PasswordInterface) {
            throw new AuthenticateException('User未实现PasswordAwareInterface');
        }

        if (!$this->hasher->verify($user->password(), $password)) {
            throw new InvalidCredentialException('密码错误');
        }

        return $user;
    }
}
