<?php

namespace App\components\cache;

use App\components\core\AbstractProvider;
use App\components\core\RequestProvider;

class CacheProvider extends AbstractProvider implements RequestProvider
{
    public static function bootRequest()
    {
        parent::bootRequest();

        if (config('redis.switch')) {
            Cache::create(\App\components\RedisPool::create(), config('cache'));
        }
    }
}
