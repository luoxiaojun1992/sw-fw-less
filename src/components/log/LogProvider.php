<?php

namespace SwFwLess\components\log;

use SwFwLess\components\provider\WorkerProviderContract;

class LogProvider implements WorkerProviderContract
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        if (\SwFwLess\components\functions\config('log.switch')) {
            Log::create(
                \SwFwLess\components\functions\config('log.path'),
                \SwFwLess\components\functions\config('log.level'),
                \SwFwLess\components\functions\config('log.sync_levels'),
                \SwFwLess\components\functions\config('log.pool_size'),
                \SwFwLess\components\functions\config('log.buffer_max_size'),
                \SwFwLess\components\functions\config('log.name'),
                \SwFwLess\components\functions\config('log.reserve_days')
            );
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
