<?php

namespace SwFwLess\components\amqp;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\WorkerProvider;

class AmqpProvider extends AbstractProvider implements WorkerProvider
{
    public static function bootWorker()
    {
        parent::bootWorker();

        if (config('amqp.switch')) {
            ConnectionPool::create();
        }
    }
}
