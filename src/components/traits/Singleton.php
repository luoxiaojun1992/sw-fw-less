<?php

namespace SwFwLess\components\traits;

trait Singleton
{
    /** @var static */
    protected static $instance;

    public static function clearInstance()
    {
        static::$instance = null;
    }

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
