<?php

namespace App\services;

use App\components\Redis;
use App\components\Response;

class DemoService
{
    public function foo()
    {
        $redisPool = Redis::create();
        $redis = $redisPool->pick();
        $result = $redis->get('key');
        $redisPool->release($redis);

        if (!$result) {
            //todo read from db
        }

        return (new Response())->setContent($result);
    }
}
