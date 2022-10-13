<?php

namespace Gaara\Accessor;

use Gaara\UserInterface;

/**
 * 超管用户接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface RootUserInterface extends UserInterface
{
    /**
     * 是否为超管用户
     *
     * @return boolean
     */
    function isRoot(): bool;
}
