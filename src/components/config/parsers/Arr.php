<?php

namespace SwFwLess\components\config\parsers;

class Arr
{
    public static function parse($configPath)
    {
        return static::mergeSpecConfigs($configPath);
    }

    protected static function mergeSpecConfigs($configPath)
    {
        $appConfig = include $configPath;

        $configPathInfo = pathinfo($configPath, PATHINFO_BASENAME | PATHINFO_DIRNAME);
        $configFileName = $configPathInfo['basename'];
        $configDir = $configPathInfo['dirname'];
        $fd = opendir($configDir);
        while($file = readdir($fd)) {
            if (!in_array($file, ['.', '..', $configFileName])) {
                $subConfigName = substr($file, 0, -4);
                $subConfig = include $configDir . '/' . $file;
                if (isset($appConfig[$subConfigName])) {
                    $appConfig[$subConfigName] = array_merge($appConfig[$subConfigName], $subConfig);
                } else {
                    $appConfig[$subConfigName] = $subConfig;
                }
            }
        }
        closedir($fd);

        return $appConfig;
    }
}
