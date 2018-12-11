<?php

namespace App\services;

use App\components\Query;
use App\components\RedisPool;
use App\components\Response;
use Swlib\SaberGM;

class DemoService extends BaseService
{
    public function redis()
    {
        $redisPool = RedisPool::create();
        $redis = $redisPool->pick();
        $result = $redis->get($this->getRequest()->param('key', 'key'));
        $redisPool->release($redis);

        return Response::output($result);
    }

    public function mysql()
    {
        $queryResult = Query::createMysql()->newSelect()
            ->from('member')
            ->cols(['*'])
            ->where('id in (111426517, 111426518)')
            ->limit(2)
            ->first();

        return Response::json($queryResult);
    }

    public function http()
    {
        $res = SaberGM::get('http://news.baidu.com/widget?ajax=json&id=ad');

        return Response::json($res->getBody());
    }
}
