<?php

namespace SwFwLess\components\swoole\coresource;

use SwFwLess\components\pool\Poolable;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\facades\ObjectPool;
use Swoole\Coroutine;

class CoroutineRes
{
    public static $coroutineRes = [];

    public static function register($res, $cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();

        Scheduler::withoutPreemptive(function () use ($cid, $res) {
            self::$coroutineRes[$cid][get_class($res)] = $res;
        });
    }

    public static function fetch($className, $cid = null)
    {
        return Scheduler::withoutPreemptive(function () use ($cid, $className) {
            return self::$coroutineRes[$cid ?? Coroutine::getCid()][$className] ?? null;
        });
    }

    public static function release($className, $cid = null, $releaseToPool = true)
    {
        $cid = $cid ?? Coroutine::getCid();

        Scheduler::withoutPreemptive(function () use ($cid, $className, $releaseToPool) {
            if (isset(self::$coroutineRes[$cid][$className])) {
                ($releaseToPool &&
                    is_object(self::$coroutineRes[$cid][$className]) &&
                    (self::$coroutineRes[$cid][$className] instanceof Poolable)
                ) && ObjectPool::release(self::$coroutineRes[$cid][$className]);
                unset(self::$coroutineRes[$cid][$className]);
            }
        });
    }

    public static function releaseAll($cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();

        Scheduler::withoutPreemptive(function () use ($cid) {
            if (isset(self::$coroutineRes[$cid])) {
                foreach (self::$coroutineRes[$cid] as $coroutineRes) {
                    is_object($coroutineRes) && ($coroutineRes instanceof Poolable) &&
                    (ObjectPool::release($coroutineRes));
                }

                unset(self::$coroutineRes[$cid]);
            }
        });
    }
}
