<?php

namespace App\components\amqp;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class AmqpProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('amqp.switch')) {
            ConnectionPool::create();
        }
    }
}
