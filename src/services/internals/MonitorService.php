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
            'redis' => config('redis.pool_change_event') && config('redis.report_pool_change') ?
                Counter::get('monitor:pool:redis') : RedisPool::countPool(),
            'mysql' => config('mysql.pool_change_event') && config('mysql.report_pool_change') ?
                Counter::get('monitor:pool:mysql') : MysqlPool::countPool(),
            'log' => [
                'pool' => Log::countPool(),
                'record_buffer' => Log::countRecordBuffer(),
            ],
            'amqp' => config('amqp.pool_change_event') && config('amqp.report_pool_change') ?
                Counter::get('monitor:pool:amqp') : AMQPConnectionPool::countPool(),
            'hbase' => config('hbase.pool_change_event') && config('hbase.report_pool_change') ?
                Counter::get('monitor:pool:hbase') : HbasePool::countPool(),
        ]);
    }

    public function swoole()
    {
        return Response::json([
            'coroutine' => Coroutine::stats(),
        ]);
    }
}
