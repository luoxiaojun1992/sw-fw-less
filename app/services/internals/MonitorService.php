<?php

namespace App\services\internals;

use App\components\Config;
use App\components\http\Response;
use App\components\utils\swoole\Counter;
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
        if (extension_loaded('swoole')) {
            return Response::json([
                'redis' => Config::get('redis.pool_change_event') && Config::get('redis.report_pool_change') ?
                    Counter::get('monitor:pool:redis') : RedisPool::countPool(),
                'mysql' => Config::get('mysql.pool_change_event') && Config::get('mysql.report_pool_change') ?
                    Counter::get('monitor:pool:mysql') : MysqlPool::countPool(),
                'log' => [
                    'pool' => Log::countPool(),
                    'record_buffer' => Log::countRecordBuffer(),
                ],
                'amqp' => Config::get('amqp.pool_change_event') && Config::get('amqp.report_pool_change') ?
                    Counter::get('monitor:pool:amqp') : AMQPConnectionPool::countPool(),
                'hbase' => Config::get('hbase.pool_change_event') && Config::get('hbase.report_pool_change') ?
                    Counter::get('monitor:pool:hbase') : HbasePool::countPool(),
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
                'hbase' => HbasePool::countPool(),
            ]);
        }
    }
}
