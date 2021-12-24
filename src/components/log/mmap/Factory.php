<?php

namespace SwFwLess\components\log\mmap;

use Monolog\Logger;
use SwFwLess\components\log\factories\FactoryContract;

class Factory implements FactoryContract
{
    public static function create(
        $logPath, $level = Logger::DEBUG, $syncLevels = [], $bubble = true,
        $filePermission = null, $recordBufferMaxSize = 10, $coroutine = false, $streamPoolSize = 100
    )
    {
        return new MonologMmapHandler(
            $logPath,
            $level,
            $syncLevels,
            $bubble,
            $filePermission,
            $recordBufferMaxSize
        );
    }
}
