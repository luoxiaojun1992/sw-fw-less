<?php

namespace SwFwLess\components\traits;

trait Singleton
{
    use SingletonInstance;

    /**
     * @return static
     * @throws \Exception
     */
    public static function create($config = [])
    {
        return static::fetchOrCreateInstance(function () use ($config) {
            return new static($config);
        });
    }
}
