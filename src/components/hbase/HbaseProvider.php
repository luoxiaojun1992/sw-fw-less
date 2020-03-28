<?php

namespace SwFwLess\components\hbase;

use SwFwLess\components\provider\AbstractProvider;

class HbaseProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (config('hbase.switch')) {
            HbasePool::create();
        }
    }
}
