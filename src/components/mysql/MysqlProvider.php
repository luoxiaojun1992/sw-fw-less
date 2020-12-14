<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\provider\AbstractProvider;

class MysqlProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        MysqlPool::create(\SwFwLess\components\functions\config('mysql'));
    }
}
