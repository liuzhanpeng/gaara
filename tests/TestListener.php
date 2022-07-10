<?php

namespace Gaara\Tests;

use Gaara\Event\EventListenerInterface;

class TestListener implements EventListenerInterface
{
    public function handle(object $event)
    {
        throw new \Exception('test listener');
    }
}
