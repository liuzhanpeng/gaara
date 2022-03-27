<?php

namespace Gaara;

/**
 * 用户提供器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface UserProviderInterface
{
    /**
     * 根据用户标识查找用户
     *
     * @param mixed $id 用户标识
     * @return UserInterface|null
     */
    function findById($id): ?UserInterface;

    /**
     * 根据登录凭证查找用户并返回, 失败抛出异常
     *
     * @param array $credential 登录凭证
     * @return UserInterface|null
     */
    function findByCredential(array $credential): ?UserInterface;
}
