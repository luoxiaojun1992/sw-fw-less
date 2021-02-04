<?php

namespace SwFwLess\components\amqp;

use SwFwLess\components\provider\WorkerProviderContract;

class AmqpProvider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        if (\SwFwLess\components\functions\config('amqp.switch')) {
            ConnectionPool::create();
        }
    }

    public static function shutdownWorker()
    {
        //
    }
}
