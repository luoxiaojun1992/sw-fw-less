<?php

namespace SwFwLess\components\log\factories;

use Lxj\Monolog\Co\Stream\Handler;
use Monolog\Logger;

class CoStreamFactory implements FactoryContract
{
    public static function create(
        $logPath, $level = Logger::DEBUG, $syncLevels = [], $bubble = true,
        $filePermission = null, $recordBufferMaxSize = 10, $coroutine = true, $streamPoolSize = 100
    )
    {
        return new Handler(
            $logPath,
            $level,
            $syncLevels,
            $bubble,
            $filePermission,
            $streamPoolSize,
            $recordBufferMaxSize,
            $coroutine
        );
    }
}
