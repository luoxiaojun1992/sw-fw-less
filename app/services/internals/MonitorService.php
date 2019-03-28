<?php

namespace App\services\internals;

use App\components\http\Response;
use App\components\swoole\counter\Counter;
use App\facades\AMQPConnectionPool;
use App\facades\HbasePool;
use App\facades\Log;
use App\facades\MysqlPool;
use App\facades\RedisPool;
use App\services\BaseService;

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
}
