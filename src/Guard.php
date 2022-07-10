<?php

namespace Gaara;

use Gaara\Event\AfterLoginEvent;
use Gaara\Event\BeforeLoginEvent;
use Gaara\Event\BeforeLogoutEvent;
use Gaara\Event\EventDispatcher;
use Gaara\Exception\AuthenticateException;

class Guard
{
    /**
     * 标识
     *
     * @var string
     */
    protected string $id;

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
     * 事件分发器
     *
     * @var EventDispatcher|null
     */
    protected ?EventDispatcher $eventDispatcher;

    /**
     * 构造
     *
     * @param string $id
     * @param AuthenticatorInterface $authenticator
     * @param AccessorInterface|null $accessor
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        string $id,
        AuthenticatorInterface $authenticator,
        ?AccessorInterface $accessor = null,
        ?EventDispatcher $eventDispatcher  = null
    ) {
        $this->id = $id;
        $this->authenticator = $authenticator;
        $this->accessor = $accessor;
        $this->eventDispatcher = $eventDispatcher;
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
        $this->dispatchEvent('before_login', new BeforeLoginEvent($credential));

        $identity = $this->authenticator->authenticate($credential);

        $this->dispatchEvent('after_login', new AfterLoginEvent($identity));

        return $identity;
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
        $this->dispatchEvent('before_logout', new BeforeLogoutEvent($this->authenticator->user()));

        $this->authenticator->clearUser();
    }

    /**
     * 当前用户是否有指定权限
     *
     * @param string $permission
     * @return boolean
     */
    public function can(string $permission): bool
    {
        if (is_null($this->accessor)) {
            throw new \Exception('未设置访问控制器');
        }

        if (!$this->isLogined()) {
            return false;
        }

        return $this->accessor->check($this->user(), $permission);
    }

    /**
     * 分发事件
     *
     * @param string $name
     * @param object $event
     * @return void
     */
    private function dispatchEvent(string $name, object $event)
    {
        if (is_null($this->eventDispatcher)) {
            return;
        }

        $name = sprintf('%s.%s', $this->id, $name);

        $this->eventDispatcher->dispatch($name, $event);
    }
}
