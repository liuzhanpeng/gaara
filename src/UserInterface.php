<?php

namespace Gaara;

/**
 * 用户接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface UserInterface
{
    /**
     * 返回用户身份唯一标识
     *
     * @return string|integer
     */
    function id();
}
