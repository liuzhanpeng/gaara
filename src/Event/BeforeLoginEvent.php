<?php

namespace Gaara\Event;

/**
 * 登录前事件
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class BeforeLoginEvent
{
    /**
     * 登录凭证
     *
     * @var array
     */
    private array $credential;

    /**
     * 构造
     *
     * @param array $credential
     */
    public function __construct(array $credential)
    {
        $this->credential = $credential;
    }

    /**
     * 返回登录凭证
     *
     * @return array
     */
    public function credential(): array
    {
        return $this->credential;
    }
}
