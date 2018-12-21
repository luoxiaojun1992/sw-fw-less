<?php

namespace App\services\internals;

use App\components\Response;
use App\facades\AMQPConnectionPool;
use App\facades\Log;
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
            'log' => [
                'pool' => Log::countPool(),
                'record_buffer' => Log::countRecordBuffer(),
            ],
            'amqp' => AMQPConnectionPool::countPool(),
        ]);
    }
}
