<?php

namespace SwFwLess\components\support\factory;

abstract class AbstractFactory
{
    public static $resolvers = [];

    /**
     * @param string $driver
     * @param callable $resolver
     */
    public static function register($driver, $resolver)
    {
        static::$resolvers[$driver] = $resolver;
    }

    public static function resolve($name)
    {
        return call_user_func(self::$resolvers[$name]);
    }
}
