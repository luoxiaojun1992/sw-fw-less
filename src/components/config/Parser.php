<?php

namespace SwFwLess\components\config;

use SwFwLess\components\config\parsers\Arr;
use SwFwLess\components\config\parsers\Jsonnet;

class Parser
{
    const PARSER_MAPPING = [
        'array' => Arr::class,
        'jsonnet' => Jsonnet::class,
    ];

    const CONFIG_SUFFIX = [
        'array' => 'php',
        'jsonnet' => 'jsonnet',
    ];

    public static function getArrConfig($configPath, $format = 'array')
    {
        $configPath = $configPath . '.' . (static::CONFIG_SUFFIX[$format] ?? 'php');
        $parser = static::PARSER_MAPPING[$format] ?? Arr::class;

        return call_user_func_array([$parser, 'parse'], [$configPath]);
    }
}
