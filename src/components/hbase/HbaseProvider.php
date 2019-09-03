<?php

namespace SwFwLess\components\hbase;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class HbaseProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (config('hbase.switch')) {
            HbasePool::create();
        }
    }
}
