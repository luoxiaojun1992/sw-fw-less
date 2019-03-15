<?php

namespace App\components\utils\swoole\counter;

class CounterProvider implements \App\components\core\ProviderContract
{
    public static function bootApp()
    {
        Counter::init();
    }

    public static function bootRequest()
    {
        //
    }
}
