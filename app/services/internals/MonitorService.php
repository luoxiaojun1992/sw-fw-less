<?php

namespace App\services\internals;

use App\components\Response;
use App\facades\MysqlPool;
use App\facades\RedisPool;
use App\services\BaseService;

class MonitorService extends BaseService
{
    public function pool()
    {
        return Response::json([
            'redis' => RedisPool::countPool(),
            'mysql' => MysqlPool::countPool(),
        ]);
    }
}
