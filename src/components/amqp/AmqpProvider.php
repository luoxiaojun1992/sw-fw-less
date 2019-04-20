<?php

namespace SwFwLess\components\amqp;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

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
