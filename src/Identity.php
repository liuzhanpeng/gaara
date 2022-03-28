<?php

namespace Gaara;

/**
 * 用户认证成功后的身份象征
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Identity
{
    /**
     * 用户
     *
     * @var UserInterface
     */
    protected UserInterface $user;

    /**
     * 象征信息
     *
     * @var array
     */
    protected array $data;

    /**
     * 构造
     *
     * @param UserInterface $user
     * @param array $data
     */
    public function __construct(UserInterface $user, array $data = [])
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * 返回用户
     *
     * @return UserInterface
     */
    public function user(): UserInterface
    {
        return $this->user;
    }

    /**
     * 返回象征信息
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }
}
