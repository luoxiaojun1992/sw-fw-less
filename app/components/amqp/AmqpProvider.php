<?php

namespace App\components\amqp;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

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
