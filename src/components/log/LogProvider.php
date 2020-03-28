<?php

namespace SwFwLess\components\log;

use SwFwLess\components\provider\AbstractProvider;

class LogProvider extends AbstractProvider
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        parent::bootWorker();
        if (config('log.switch')) {
            Log::create(
                config('log.path'),
                config('log.level'),
                config('log.pool_size'),
                config('log.buffer_max_size'),
                config('log.name'),
                config('log.reserve_days')
            );
        }
    }
}
