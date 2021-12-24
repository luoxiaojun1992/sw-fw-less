<?php

namespace SwFwLess\components\log;

use Monolog\Logger;
use SwFwLess\components\log\factories\CoStreamFactory;
use SwFwLess\components\log\mmap\Factory;

class DriverFactory
{
    const FACTORY_MAPPINGS = [
        Log::DRIVER_CO_STREAM => CoStreamFactory::class,
        Log::DRIVER_MEMORY_MAP => Factory::class,
    ];

    public static function create(
        $logPath, $driver = Log::DRIVER_DEFAULT, $level = Logger::DEBUG, $syncLevels = [], $bubble = true,
        $filePermission = null, $recordBufferMaxSize = 10, $coroutine = false, $streamPoolSize = 100
    )
    {
        $factory = static::FACTORY_MAPPINGS[$driver] ?? static::FACTORY_MAPPINGS[Log::DRIVER_DEFAULT];

        return call_user_func_array(
            [$factory, 'create'],
            [
                'logPath' => $logPath,
                'level' => $level,
                'syncLevels' => $syncLevels,
                'bubble' => $bubble,
                'filePermission' => $filePermission,
                'recordBufferMaxSize' => $recordBufferMaxSize,
                'coroutine' => $coroutine,
                'streamPoolSize' => $streamPoolSize,
            ]
        );
    }
}
