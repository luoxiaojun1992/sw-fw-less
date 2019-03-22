<?php

namespace App\components\utils\swoole\coresource;

use App\components\core\AbstractProvider;

class CoroutineResProvider extends AbstractProvider
{
    public static function shutdown()
    {
        parent::shutdown();

        CoroutineRes::releaseAll();
    }
}
