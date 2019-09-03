<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class MysqlProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        MysqlPool::create(config('mysql'));
    }
}
