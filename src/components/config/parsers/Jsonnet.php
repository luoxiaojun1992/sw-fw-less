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
                $subConfigPath = $configDir . '/' . $file;
                $subConfigPathInfo = pathinfo($subConfigPath, PATHINFO_EXTENSION | PATHINFO_FILENAME);
                $subConfigSuffix = $subConfigPathInfo['extension'];
                if ($subConfigSuffix === 'jsonnet') {
                    $subConfig = \Jsonnet::evaluateFile($subConfigPath);
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
