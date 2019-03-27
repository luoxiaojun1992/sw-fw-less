<?php

namespace App\components\swoole\coresource;

use App\components\provider\AbstractProvider;

class CoroutineResProvider extends AbstractProvider
{
    public static function shutdown()
    {
        parent::shutdown();

        CoroutineRes::releaseAll();
    }
}
