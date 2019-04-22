<?php

namespace SwFwLess\components\cache;

use SwFwLess\components\provider\AbstractProvider;
use SwFwLess\components\provider\RequestProvider;

class CacheProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        Cache::create(\SwFwLess\components\redis\RedisPool::create(), config('cache'));
    }
}
