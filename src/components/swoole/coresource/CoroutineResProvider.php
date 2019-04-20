<?php

namespace SwFwLess\components\swoole\coresource;

use SwFwLess\components\provider\AbstractProvider;

class CoroutineResProvider extends AbstractProvider
{
    public static function shutdown()
    {
        parent::shutdown();

        CoroutineRes::releaseAll();
    }
}
