<?php

namespace SwFwLess\components;

use SwFwLess\components\config\Parser;
use SwFwLess\components\swoole\Scheduler;

class Config
{
    private static $config = [];

    private static $configCache = [];

    public static function initByArr($arrConfig)
    {
        static::$config = $arrConfig;
    }

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
            if (is_string($key)) {
                if (array_key_exists($key, self::$configCache)) {
                    return self::$configCache[$key];
                }
            }

            if (!is_string($key) && !is_array($key) && !is_int($key)) {
                return $default;
            }

            if (Helper::nestedArrHas(static::$config, $key)) {
                $config = Helper::nestedArrGet(static::$config, $key, $default);
                if (is_string($key)) {
                    self::$configCache[$key] = $config;
                }
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
        if (is_string($key)) {
            Scheduler::withoutPreemptive(function () use ($key, $value) {
                static::$configCache[$key] = $value;
            });
        }
    }

    public static function forget($key)
    {
        if (!is_string($key) && !is_array($key) && !is_int($key)) {
            return;
        }

        Helper::nestedArrForget(static::$config, $key);
        if (is_string($key)) {
            Scheduler::withoutPreemptive(function () use ($key) {
                unset(static::$configCache[$key]);
            });
        }
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
