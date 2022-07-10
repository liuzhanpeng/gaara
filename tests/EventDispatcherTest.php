<?php

namespace Gaara\Tests;

use Gaara\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    public function testBasic()
    {
        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener('after_login', function () {
            throw new \Exception('after login');
        });

        $this->expectExceptionMessage('after login');

        $eventDispatcher->dispatch('after_login', new \stdClass);
    }

    public function testBasic2()
    {
        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener('after_login', TestListener::class);

        $this->expectExceptionMessage('test listener');

        $eventDispatcher->dispatch('after_login', new \stdClass);
    }
}
