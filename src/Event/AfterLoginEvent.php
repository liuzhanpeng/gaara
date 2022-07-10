<?php

namespace Gaara\Event;

use Gaara\Identity;

/**
 * 登录后事件
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class AfterLoginEvent
{
    /**
     * 身份对象
     *
     * @var Identity
     */
    private Identity $identity;

    /**
     * 构造
     *
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * 返回身份对象
     *
     * @return Identity
     */
    public function identity(): Identity
    {
        return $this->identity;
    }
}
