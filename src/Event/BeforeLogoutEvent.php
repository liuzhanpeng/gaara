<?php

namespace Gaara\Event;

use Gaara\UserInterface;

/**
 * 登出前事件
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class BeforeLogoutEvent
{
    /**
     * 用户
     *
     * @var UserInterface
     */
    private UserInterface $user;

    /**
     * 构造
     *
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * 返回用户
     *
     * @return UserInterface
     */
    public function identity(): UserInterface
    {
        return $this->user;
    }
}
