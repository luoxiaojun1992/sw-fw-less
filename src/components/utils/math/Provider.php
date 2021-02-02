<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\provider\WorkerProviderContract;

class Provider implements WorkerProviderContract
{
    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        Math::create(\SwFwLess\components\functions\config('util.math'));
    }

    public static function shutdownWorker()
    {
        //
    }
}
