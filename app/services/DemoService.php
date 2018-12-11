<?php

namespace App\services;

use App\components\Query;
use App\components\Redis;
use App\components\Response;

class DemoService extends BaseService
{
    public function foo()
    {
        $redisPool = Redis::create();
        $redis = $redisPool->pick();
        $result = $redis->get($this->getRequest()->param('key', 'key'));
        $redisPool->release($redis);

        if (!$result) {
            $queryResult = Query::createMysql()->newSelect()
                ->from('test')
                ->cols(['id'])
                ->limit(1)
                ->execute();
        }

        return Response::output($result);
    }
}
