<?php

namespace Gaara\Authenticator;

use Gaara\Identity;
use Gaara\UserInterface;

/**
 * 基于Session的认证器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class SessionAuthenticator extends AbstractAuthenticator
{
    /**
     * Session实例
     *
     * @var SessionInterface
     */
    protected SessionInterface $session;

    /**
     * 会话key
     *
     * @var string
     */
    protected string $sessionKey;

    /**
     * 构造
     *
     * @param SessionInterface $session Session实例
     * @param string $sessionKey 会话key
     */
    public function __construct(SessionInterface $session, string $sessionKey)
    {
        $this->session = $session;
        $this->sessionKey = $sessionKey;
    }

    /**
     * @inheritDoc
     */
    public function setUser(UserInterface $user): Identity
    {
        $this->session->set($this->sessionKey, $user->id());

        return new Identity($user);
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool
    {
        return $this->session->has($this->sessionKey);
    }

    /**
     * @inheritDoc
     */
    public function user(): ?UserInterface
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $id = $this->session->get($this->sessionKey);

        return $this->userProvider->findById($id);
    }

    /**
     * @inheritDoc
     */
    public function clearUser()
    {
        $this->session->delete($this->sessionKey);
    }
}
