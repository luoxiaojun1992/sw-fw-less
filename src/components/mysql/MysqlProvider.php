<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class MysqlProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('mysql.switch')) {
            MysqlPool::create(
                config('mysql.dsn'),
                config('mysql.username'),
                config('mysql.passwd'),
                config('mysql.options'),
                config('mysql.pool_size')
            );
        }
    }
}
