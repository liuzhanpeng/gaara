<?php

namespace Gaara\Tests;

use Gaara\Authenticator\SessionInterface;

class Session implements SessionInterface
{
    private array $items;

    public function set(string $key, $data)
    {
        $this->items[$key] = $data;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(string $key)
    {
        if ($this->has($key)) {
            return null;
        }

        return $this->items[$key];
    }

    public function delete(string $key)
    {
        unset($this->items[$key]);
    }
}
