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
        return (self::$instance instanceof self) ? (self::$instance) : (self::$instance = new self());
    }
}
