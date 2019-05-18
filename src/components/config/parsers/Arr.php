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
                $subConfigPath = $configDir . '/' . $file;
                $subConfigPathInfo = pathinfo($subConfigPath, PATHINFO_EXTENSION | PATHINFO_FILENAME);
                $subConfigSuffix = $subConfigPathInfo['extension'];
                if ($subConfigSuffix === 'php') {
                    $subConfig = include $subConfigPath;
                    $subConfigName = $subConfigPathInfo['filename'];
                    if (isset($appConfig[$subConfigName])) {
                        $appConfig[$subConfigName] = array_merge($appConfig[$subConfigName], $subConfig);
                    } else {
                        $appConfig[$subConfigName] = $subConfig;
                    }
                }
            }
        }
        closedir($fd);

        return $appConfig;
    }
}
