<?php

namespace App\components\swoole\counter;

use App\components\provider\AbstractProvider;
use App\components\provider\AppProvider;

class CounterProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        Counter::init();
    }
}
