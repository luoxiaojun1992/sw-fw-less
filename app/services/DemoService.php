<?php

namespace App\services;

use App\components\Redis;

class DemoService
{
    public function foo()
    {
        $redisPool = Redis::create();
        $redis = $redisPool->pick();
        $result = $redis->get('key');
        $redisPool->release($redis);
        return $result;
    }
}
