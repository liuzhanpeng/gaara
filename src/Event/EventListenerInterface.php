<?php

namespace Gaara\Event;

use Gaara\Exception\GaaraException;

/**
 * 事件监听器接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface EventListenerInterface
{
    /**
     * 处理事件
     *
     * @param object $event 事件对象
     * @return void
     * @throws GaaraException
     */
    function handle(object $event);
}
