<?php

namespace SwFwLess\components\amqp;

use SwFwLess\components\provider\AbstractProvider;

class AmqpProvider extends AbstractProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (\SwFwLess\components\functions\config('amqp.switch')) {
            ConnectionPool::create();
        }
    }
}
