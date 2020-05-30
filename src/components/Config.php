<?php

namespace SwFwLess\components;

use SwFwLess\components\config\Parser;
use SwFwLess\components\swoole\Scheduler;

class Config
{
    private static $config = [];

    private static $configCache = [];

    /**
     * @param $configPath
     * @param string $format
     */
    public static function init($configPath, $format = 'array')
    {
        static::$config = Parser::getArrConfig($configPath, $format);
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Scheduler::withoutPreemptive(function () use ($key, $default) {
            if (array_key_exists($key, self::$configCache)) {
                return self::$configCache[$key];
            }

            if (!is_string($key) && !is_array($key) && !is_int($key)) {
                return $default;
            }

            if (Helper::nestedArrHas(static::$config, $key)) {
                $config = Helper::nestedArrGet(static::$config, $key, $default);
                self::$configCache[$key] = $config;
            } else {
                $config = $default;
            }

            return $config;
        });
    }

    public static function set($key, $value)
    {
        if (!is_string($key) && !is_array($key) && !is_int($key)) {
            return;
        }

        Helper::nestedArrSet(static::$config, $key, $value);
    }

    public static function forget($key)
    {
        if (!is_string($key) && !is_array($key) && !is_int($key)) {
            return;
        }

        Helper::nestedArrForget(static::$config, $key);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public static function has($key)
    {
        if (!is_string($key) && !is_array($key) && !is_int($key)) {
            return false;
        }

        return Helper::nestedArrHas(static::$config, $key);
    }
}
