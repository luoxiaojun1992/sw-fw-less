<?php

namespace SwFwLess\components\config\parsers;

class Jsonnet
{
    public static function parse($configPath)
    {
        return static::mergeSpecConfigs($configPath);
    }

    protected static function mergeSpecConfigs($configPath)
    {
        $appConfig = \Jsonnet::evaluateFile($configPath);

        $configPathInfo = pathinfo($configPath, PATHINFO_BASENAME | PATHINFO_DIRNAME);
        $configFileName = $configPathInfo['basename'];
        $configDir = $configPathInfo['dirname'];
        $fd = opendir($configDir);
        while($file = readdir($fd)) {
            if (!in_array($file, ['.', '..', $configFileName])) {
                $subConfigName = substr($file, 0, -8);
                $subConfig = \Jsonnet::evaluateFile($configDir . '/' . $file);
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
