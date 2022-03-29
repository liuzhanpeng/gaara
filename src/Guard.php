<?php

namespace Gaara;

use Gaara\Exception\AuthenticateException;

class Guard
{
    /**
     * 认证器
     *
     * @var AuthenticatorInterface
     */
    protected AuthenticatorInterface $authenticator;

    /**
     * 访问控制器
     *
     * @var AccessorInterface
     */
    protected ?AccessorInterface $accessor;

    /**
     * 构造
     *
     * @param AuthenticatorInterface $authenticator
     * @param AccessorInterface|null $accessor
     */
    public function __construct(
        AuthenticatorInterface $authenticator,
        ?AccessorInterface $accessor
    ) {
        $this->authenticator = $authenticator;
        $this->accessor = $accessor;
    }

    /**
     * 登录
     *
     * @param array $credential
     * @return Identity
     * @throws AuthenticateException
     */
    public function login(array $credential): Identity
    {
        return $this->authenticator->authenticate($credential);
    }

    /**
     * 直接以用户身份登录
     *
     * @param UserInterface $user
     * @return Identity
     * @throws AuthenticateException
     */
    public function loginAs(UserInterface $user): Identity
    {
        return $this->authenticator->setUser($user);
    }

    /**
     * 当前用户是否已登录
     *
     * @return boolean
     */
    public function isLogined(): bool
    {
        return $this->authenticator->isAuthenticated();
    }

    /**
     * 返回用户标识
     *
     * @return string|integer|null
     */
    public function id()
    {
        return $this->authenticator->id();
    }

    /**
     * 返回当前用户
     *
     * @return UserInterface|null
     */
    public function user(): ?UserInterface
    {
        return $this->authenticator->user();
    }

    /**
     * 当前用户登出
     *
     * @return void
     */
    public function logout()
    {
        $this->authenticator->clearUser();
    }

    /**
     * 当前用户是否有指定权限
     *
     * @param string $permission
     * @return boolean
     */
    public function can($permission): bool
    {
        if (is_null($this->accessor)) {
            throw new \Exception('未设置访问控制器');
        }

        if (!$this->isLogined()) {
            return false;
        }

        return $this->accessor->check($this->user(), $permission);
    }
}
