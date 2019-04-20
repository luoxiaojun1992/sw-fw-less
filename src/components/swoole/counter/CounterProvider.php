<?php

namespace SwFwLess\components\swoole\counter;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\AppProvider;
use SwFwLess\components\provider\RequestProvider;

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
