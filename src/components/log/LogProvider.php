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
        if (\SwFwLess\components\functions\config('log.switch')) {
            Log::create(
                \SwFwLess\components\functions\config('log.path'),
                \SwFwLess\components\functions\config('log.level'),
                \SwFwLess\components\functions\config('log.pool_size'),
                \SwFwLess\components\functions\config('log.buffer_max_size'),
                \SwFwLess\components\functions\config('log.name'),
                \SwFwLess\components\functions\config('log.reserve_days')
            );
        }
    }
}
