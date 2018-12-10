<?php

namespace App\services;

use App\components\Mysql;
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
            $mysqlPool = Mysql::create();
            $pdo = $mysqlPool->pick();
            $statement = $pdo->query('select * from test where id = 1');
            if ($statement) {
                $result = $statement->fetch();
                var_dump($result);
            }
        }

        return (new Response())->setContent($result);
    }
}
