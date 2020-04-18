<?php

namespace SwFwLess\components\swoole\coresource;

use SwFwLess\components\swoole\Scheduler;
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

    public static function release($className, $cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();

        Scheduler::withoutPreemptive(function () use ($cid, $className) {
            if (isset(self::$coroutineRes[$cid][$className])) {
                unset(self::$coroutineRes[$cid][$className]);
            }
        });
    }

    public static function releaseAll($cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();

        Scheduler::withoutPreemptive(function () use ($cid) {
            if (isset(self::$coroutineRes[$cid])) {
                unset(self::$coroutineRes[$cid]);
            }
        });
    }
}
