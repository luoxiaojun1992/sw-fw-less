<?php

namespace SwFwLess\components\swoole;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\AppProvider;

class SwooleProvider extends AbstractProvider implements AppProvider
{
    public static function bootApp()
    {
        parent::bootApp();

        \Co::set(config('coroutine'));
    }
}
