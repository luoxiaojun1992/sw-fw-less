<?php

namespace App\components;

class Config
{
    private static $config = [];

    /**
     * @param $config
     */
    public static function init($config)
    {
        self::$config = $config;
    }

    /**
     * @param $key
     * @param null $default
     * @return array|mixed|null
     */
    public static function get($key, $default = null)
    {
        if (!$key) {
            return $default;
        }
        if (!is_string($key) && !is_array($key)) {
            return $default;
        }

        return Helper::nestedArrGet(self::$config, $key, $default);
    }
}
