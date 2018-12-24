<?php

namespace App\services\internals;

use App\components\Response;
use App\components\utils\swoole\Counter;
use App\facades\AMQPConnectionPool;
use App\facades\Log;
use App\facades\MysqlPool;
use App\facades\RedisPool;
use App\services\BaseService;

class MonitorService extends BaseService
{
    public function pool()
    {
        if (extension_loaded('swoole')) {
            return Response::json([
                'redis' => Counter::get('monitor:pool:redis'),
                'mysql' => Counter::get('monitor:pool:mysql'),
                'log' => [
                    'pool' => Log::countPool(),
                    'record_buffer' => Log::countRecordBuffer(),
                ],
                'amqp' => Counter::get('monitor:pool:amqp'),
            ]);
        } else {
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
}
