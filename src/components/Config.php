<?php

namespace SwFwLess\components;

class Config
{
    private static $config = [];

    /**
     * @param $config
     */
    public static function init($config)
    {
        static::$config = self::mergeSpecConfigs($config);
    }

    private static function mergeSpecConfigs($appConfig)
    {
        $fd = opendir(APP_BASE_PATH . 'config');
        while($file = readdir($fd)) {
            if (!in_array($file, ['.', '..', 'app.php'])) {
                $configName = substr($file, 0, -4);
                if (isset($appConfig[$configName])) {
                    $appConfig[$configName] = array_merge($appConfig[$configName], require APP_BASE_PATH . 'config/' . $file);
                } else {
                    $appConfig[$configName] = require APP_BASE_PATH . 'config/' . $file;
                }
            }
        }
        closedir($fd);

        return $appConfig;
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
