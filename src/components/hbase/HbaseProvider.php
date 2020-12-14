<?php

namespace SwFwLess\components\hbase;

use SwFwLess\components\provider\AbstractProvider;

class HbaseProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (\SwFwLess\components\functions\config('hbase.switch')) {
            HbasePool::create();
        }
    }
}
