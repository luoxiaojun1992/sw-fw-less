<?php

namespace SwFwLess\components\traits;

trait SingletonInstance
{
    /** @var static */
    protected static $instance;

    public static function clearInstance()
    {
        static::$instance = null;
    }

    public static function fetchOrCreateInstance($creator)
    {
        return (self::$instance instanceof self) ? (self::$instance) : (self::$instance = call_user_func($creator));
    }
}
