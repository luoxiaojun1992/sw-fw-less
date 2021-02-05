<?php

namespace SwFwLess\components\hbase;

use SwFwLess\components\provider\WorkerProviderContract;

class HbaseProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        if (\SwFwLess\components\functions\config('hbase.switch')) {
            HbasePool::create();
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
