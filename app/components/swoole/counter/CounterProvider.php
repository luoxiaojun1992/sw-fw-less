<?php

namespace App\components\swoole\counter;

use App\components\provider\AbstractProvider;
use App\components\provider\AppProvider;
use App\components\provider\RequestProvider;

class CounterProvider extends AbstractProvider implements AppProvider, RequestProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        Counter::init();
    }

    public static function bootRequest()
    {
        parent::bootRequest();

        Counter::reload();
    }
}
