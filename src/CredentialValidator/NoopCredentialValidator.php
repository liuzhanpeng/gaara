<?php

namespace Gaara\CredentialValidator;

use Gaara\CredentialValidatorInterface;
use Gaara\Exception\InvalidCredentialException;
use Gaara\UserInterface;
use Gaara\UserProviderInterface;

/**
 * 空操作的登录凭证验证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class NoopCredentialValidator implements CredentialValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function validateCredential(UserProviderInterface $userProvider, array $credential): UserInterface
    {
        $user = $userProvider->findByCredential($credential);
        if (is_null($user)) {
            throw new InvalidCredentialException('无效登录凭证');
        }

        return $user;
    }
}
