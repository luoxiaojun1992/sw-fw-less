<?php

namespace SwFwLess\components\swoole\coresource;

use SwFwLess\components\provider\RequestProviderContract;

class CoroutineResProvider implements RequestProviderContract
{
    public static function bootRequest()
    {
        //
    }

    public static function shutdownResponse()
    {
        CoroutineRes::releaseAll();
    }
}
