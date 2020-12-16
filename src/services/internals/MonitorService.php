<?php

namespace SwFwLess\services\internals;

use SwFwLess\components\http\Response;
use SwFwLess\components\swoole\counter\Counter;
use SwFwLess\components\swoole\Server;
use SwFwLess\facades\AMQPConnectionPool;
use SwFwLess\facades\HbasePool;
use SwFwLess\facades\Log;
use SwFwLess\facades\MysqlPool;
use SwFwLess\facades\ObjectPool;
use SwFwLess\facades\RedisPool;
use SwFwLess\services\BaseService;
use Swoole\Coroutine;

class MonitorService extends BaseService
{
    public function pool()
    {
        $swServer = Server::getInstance();

        return Response::json([
            'worker' => [
                'id' => $swServer->worker_id,
                'pid' => $swServer->worker_pid,
            ],
            'redis' => \SwFwLess\components\functions\config('redis.pool_change_event') &&
            \SwFwLess\components\functions\config('redis.report_pool_change') ?
                Counter::get('monitor:pool:redis') : RedisPool::countPool(),
            'mysql' => \SwFwLess\components\functions\config('mysql.pool_change_event') &&
            \SwFwLess\components\functions\config('mysql.report_pool_change') ?
                Counter::get('monitor:pool:mysql') : MysqlPool::countPool(),
            'log' => [
                'pool' => Log::countPool(),
                'record_buffer' => Log::countRecordBuffer(),
                'worker_id' => $swServer->worker_id,
                'worker_pid' => $swServer->worker_pid,
            ],
            'amqp' => \SwFwLess\components\functions\config('amqp.pool_change_event') &&
            \SwFwLess\components\functions\config('amqp.report_pool_change') ?
                Counter::get('monitor:pool:amqp') : AMQPConnectionPool::countPool(),
            'hbase' => \SwFwLess\components\functions\config('hbase.pool_change_event') &&
            \SwFwLess\components\functions\config('hbase.report_pool_change') ?
                Counter::get('monitor:pool:hbase') : HbasePool::countPool(),
            'object' => ObjectPool::stats(),
        ]);
    }

    public function swoole()
    {
        return Response::json([
            'swoole' => Server::getInstance()->stats(),
            'coroutine' => Coroutine::stats(),
        ]);
    }

    public function cpu()
    {
        $swServer = Server::getInstance();

        return Response::json([
            'worker_id' => $swServer->worker_id,
            'worker_pid' => $swServer->worker_pid,
            'sys_load' => sys_getloadavg(),
        ]);
    }

    public function memory()
    {
        $swServer = Server::getInstance();

        return Response::json([
            'usage' => memory_get_usage(),
            'real_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(),
            'peak_real_usage' => memory_get_peak_usage(true),
            'worker_id' => $swServer->worker_id,
            'worker_pid' => $swServer->worker_pid,
        ]);
    }
}
