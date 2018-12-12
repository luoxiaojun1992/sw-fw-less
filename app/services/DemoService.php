<?php

namespace App\services;

use App\components\RedisPool;
use App\components\Response;
use App\facades\Log;
use App\models\Member;
use Swlib\SaberGM;

class DemoService extends BaseService
{
    public function redis()
    {
        $redisPool = RedisPool::create();
        /** @var \Redis $redis */
        $redis = $redisPool->pick();
        $result = $redis->get($this->getRequest()->param('key', 'key'));
        $redisPool->release($redis);

        Log::info('test');

        return Response::output($result);
    }

    public function mysql()
    {
        $queryResult = Member::select()
            ->cols(['*'])
            ->where('id in (111426517, 111426518)')
            ->limit(2)
            ->first()
            ->toArray();

        return Response::json($queryResult);
    }

    public function http()
    {
        $res = SaberGM::get('http://news.baidu.com/widget?ajax=json&id=ad');

        return Response::json($res->getBody());
    }
}
