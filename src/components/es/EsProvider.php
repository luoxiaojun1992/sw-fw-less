<?php

namespace SwFwLess\components\es;

use SwFwLess\components\provider\WorkerProviderContract;

class EsProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        if (\SwFwLess\components\functions\config('elasticsearch.switch')) {
            Manager::create();
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
