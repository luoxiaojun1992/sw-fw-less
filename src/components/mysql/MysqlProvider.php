<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class MysqlProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        MysqlPool::create(config('mysql'));
    }
}
