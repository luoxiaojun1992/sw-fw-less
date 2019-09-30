<?php

namespace SwFwLess\components\config;

use SwFwLess\components\config\parsers\Apollo;
use SwFwLess\components\config\parsers\Arr;
use SwFwLess\components\config\parsers\Jsonnet;

class Parser
{
    const TYPE_ARRAY = 'array';
    const TYPE_JSONNET = 'jsonnet';
    const TYPE_APOLLO = 'apollo';

    const PARSER_MAPPING = [
        self::TYPE_ARRAY => Arr::class,
        self::TYPE_JSONNET => Jsonnet::class,
        self::TYPE_APOLLO => Apollo::class,
    ];

    public static function getArrConfig($configPath, $format = 'array')
    {
        $formatParts = explode(',' , $format);

        $apolloIndex = array_search(self::TYPE_APOLLO, $formatParts);
        if ($apolloIndex !== false) {
            unset($formatParts[$apolloIndex]);
            array_push($formatParts, self::TYPE_APOLLO);
        }

        $appConfig = [];

        foreach ($formatParts as $formatPart) {
            $parser = static::PARSER_MAPPING[$formatPart] ?? Arr::class;
            $parserConfig = [$configPath];
            if ($formatPart === self::TYPE_APOLLO) {
                $parserConfig = [$appConfig];
            }
            foreach (call_user_func_array([$parser, 'parse'], $parserConfig) as $key => $value) {
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
