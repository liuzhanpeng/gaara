<?php

namespace Gaara\Accessor;

use Gaara\UserInterface;

/**
 * 用户权限提供器
 * 
 * @@author lzpeng <liuzhanpeng@gmail.com>
 */
interface PermissionProviderInterface
{
    /**
     * 返回用户权限列表
     *
     * @param UserInterface $user
     * @return array
     */
    function permissions(UserInterface $user): array;
}
