<?php

namespace SwFwLess\components\log\factories;

use Monolog\Logger;

interface FactoryContract
{
    public static function create(
        $logPath, $level = Logger::DEBUG, $syncLevels = [], $bubble = true,
        $filePermission = null, $recordBufferMaxSize = 10, $coroutine = false, $streamPoolSize = 100
    );
}
