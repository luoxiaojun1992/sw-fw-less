<?php

namespace App\components\traits;

trait Singleton
{
    /** @var static */
    private static $instance;

    /**
     * @return static
     * @throws \Exception
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self();
    }
}
