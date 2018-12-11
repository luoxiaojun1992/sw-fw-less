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
        if (!is_string($key)) {
            return null;
        }
        if (!$key) {
            return null;
        }

        $arr = self::$config;
        $keys = explode('.', $key);
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                $arr = $arr[$key];
            } else {
                $arr = null;
            }
        }

        return $arr;
    }
}
