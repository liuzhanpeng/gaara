<?php

namespace Gaara\Event;

use Gaara\Exception\GaaraException;
use Psr\Container\ContainerInterface;

/**
 * 事件分发器
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class EventDispatcher
{
    /**
     * 已注册事件列表
     *
     * @var array
     */
    private array $events;

    /**
     * DI容器
     *
     * @var ContainerInterface|null
     */
    private ?ContainerInterface $container;

    /**
     * 构造
     *
     * @param ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * 注册监听器
     *
     * @param string $name 标识
     * @param string|callable $listener 事件监听器
     * @return void
     */
    public function addListener(string $name, $listener)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = [];
        }

        $this->events[$name][] = $listener;
    }

    /**
     * 分发事件
     *
     * @param string $name 标识
     * @param object $event 事件
     * @return void
     */
    public function dispatch(string $name, object $event)
    {
        if (!isset($this->events[$name])) {
            return;
        }

        foreach ($this->events[$name] as $listener) {
            if (is_callable($listener)) {
                call_user_func($listener, $event);
            } elseif (is_string($listener)) {
                $instance = null;
                if (!is_null($this->container)) {
                    $instance = $this->container->get($listener);
                }
                if (is_null($instance)) {
                    $instance = new $listener;
                }

                if (!$instance instanceof EventListenerInterface) {
                    throw new GaaraException('事件监听器必须实现EventListenerInterface');
                }

                $instance->handle($event);
            } else {
                throw new GaaraException('未支持的事件监听器类型');
            }
        }
    }
}
