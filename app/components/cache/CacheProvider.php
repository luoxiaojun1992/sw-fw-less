<?php

namespace App\components\cache;

use App\components\provider\AbstractProvider;
use App\components\provider\RequestProvider;

class CacheProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            Cache::create(\App\components\redis\RedisPool::create(), config('cache'));
        }
    }
}
