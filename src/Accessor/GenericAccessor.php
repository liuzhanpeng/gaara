<?php

namespace Gaara\Accessor;

use Gaara\AccessorInterface;
use Gaara\UserInterface;

/**
 * 通用访问控制器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class GenericAccessor implements AccessorInterface
{
    /**
     * 用户权限提供器
     *
     * @var PermissionProviderInterface
     */
    protected PermissionProviderInterface $permissionProvider;

    /**
     * 构造
     *
     * @param PermissionProviderInterface $userProvider
     */
    public function __construct(PermissionProviderInterface $permissionProvider)
    {
        $this->permissionProvider = $permissionProvider;
    }

    /**
     * @inheritDoc
     */
    public function check(UserInterface $user, string $permission): bool
    {
        foreach ($this->permissionProvider->permissions($user) as $item) {
            if ($item === $permission) {
                return true;
            }
        }

        return false;
    }
}
