<?php

namespace Gaara;

/**
 * 访问控制器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface AccessorInterface
{
    /**
     * 检查用户是否有指定权限
     *
     * @param UserInterface $user
     * @param string $permission
     * @return boolean
     */
    function check(UserInterface $user, string $permission): bool;
}
