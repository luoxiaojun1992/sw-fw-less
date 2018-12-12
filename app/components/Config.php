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
     * @return array|mixed|null
     */
    public static function get($key)
    {
        if (!$key) {
            return null;
        }
        if (!is_string($key) && !is_array($key)) {
            return null;
        }

        return Helper::nestedArrGet(self::$config, $key);
    }
}
