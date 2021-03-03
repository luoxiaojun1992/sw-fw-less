<?php

namespace SwFwLess\components\time\ntp;

use SwFwLess\components\provider\WorkerProviderContract;

class Provider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        Time::create(
            \SwFwLess\components\functions\config('time.ntp')
        );
    }

    public static function shutdownWorker()
    {
        //
    }
}
