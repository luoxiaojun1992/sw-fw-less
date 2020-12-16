<?php

namespace SwFwLess\services\internals;

use SwFwLess\components\http\Response;
use SwFwLess\components\swoole\counter\Counter;
use SwFwLess\facades\AMQPConnectionPool;
use SwFwLess\facades\HbasePool;
use SwFwLess\facades\Log;
use SwFwLess\facades\MysqlPool;
use SwFwLess\facades\RedisPool;
use SwFwLess\services\BaseService;
use Swoole\Coroutine;

class MonitorService extends BaseService
{
    public function pool()
    {
        return Response::json([
            'redis' => \SwFwLess\components\functions\config('redis.pool_change_event') &&
            \SwFwLess\components\functions\config('redis.report_pool_change') ?
                Counter::get('monitor:pool:redis') : RedisPool::countPool(),
            'mysql' => \SwFwLess\components\functions\config('mysql.pool_change_event') &&
            \SwFwLess\components\functions\config('mysql.report_pool_change') ?
                Counter::get('monitor:pool:mysql') : MysqlPool::countPool(),
            'log' => [
                'pool' => Log::countPool(),
                'record_buffer' => Log::countRecordBuffer(),
            ],
            'amqp' => \SwFwLess\components\functions\config('amqp.pool_change_event') &&
            \SwFwLess\components\functions\config('amqp.report_pool_change') ?
                Counter::get('monitor:pool:amqp') : AMQPConnectionPool::countPool(),
            'hbase' => \SwFwLess\components\functions\config('hbase.pool_change_event') &&
            \SwFwLess\components\functions\config('hbase.report_pool_change') ?
                Counter::get('monitor:pool:hbase') : HbasePool::countPool(),
        ]);
    }

    public function swoole()
    {
        return Response::json([
            'coroutine' => Coroutine::stats(),
        ]);
    }

    public function memory()
    {
        return Response::json([
            'usage' => memory_get_usage()
        ]);
    }
}
