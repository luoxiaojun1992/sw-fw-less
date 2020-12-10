<?php

namespace SwFwLess\components\pool;

use SwFwLess\components\provider\AbstractProvider;

class Provider extends AbstractProvider
{
    public static function bootWorker()
    {
        ObjectPool::create(\SwFwLess\components\functions\config('pool'));
    }
}
