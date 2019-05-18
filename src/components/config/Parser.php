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

    public static function getArrConfig($configPath, $format = 'array')
    {
        $formatParts = explode(',' , $format);

        $appConfig = [];
        foreach ($formatParts as $formatPart) {
            $parser = static::PARSER_MAPPING[$formatPart] ?? Arr::class;
            foreach (call_user_func_array([$parser, 'parse'], [$configPath]) as $key => $value) {
                if (isset($appConfig[$key])) {
                    $appConfig[$key] = array_merge($appConfig[$key], $value);
                } else {
                    $appConfig[$key] = $value;
                }
            }
        }

        return $appConfig;
    }
}
