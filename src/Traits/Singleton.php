<?php

namespace Prhost\Epub3\Traits;

trait Singleton
{
    private static $instance = null;

    protected function __construct()
    {
    }

    public static function getInstance(): static
    {
        if (self::$instance == null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    protected function __clone()
    {
    }
}
