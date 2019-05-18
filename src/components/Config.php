<?php

namespace SwFwLess\components;

use SwFwLess\components\config\Parser;

class Config
{
    private static $config = [];

    /**
     * @param $configPath
     * @param string $format
     */
    public static function init($configPath, $format = 'array')
    {
        static::$config = Parser::getArrConfig($configPath, $format);
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

        return Helper::nestedArrGet(static::$config, $key, $default);
    }
}
