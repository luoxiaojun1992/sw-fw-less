<?php

namespace SwFwLess\components\swoole;

use SwFwLess\components\provider\AppProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;

class SwooleProvider implements AppProviderContract, WorkerProviderContract
{
    /**
     * @throws \Exception
     */
    protected static function setCoroutineConfig()
    {
        $coroutineConfig = \SwFwLess\components\functions\config('coroutine');
        if ($coroutineConfig['enable_preemptive_scheduler']) {
            throw new \Exception('Preemptive coroutine scheduler has not been supported.');
        }
        \Co::set($coroutineConfig);
    }

    /**
     * @throws \Exception
     */
    public static function bootApp()
    {
        self::setCoroutineConfig();
    }

    public static function shutdownApp()
    {
        //
    }

    /**
     * @throws \Exception
     */
    public static function bootWorker()
    {
        self::setCoroutineConfig();
    }

    public static function shutdownWorker()
    {
        //
    }
}
