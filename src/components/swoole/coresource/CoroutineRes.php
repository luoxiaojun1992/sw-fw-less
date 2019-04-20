<?php

namespace SwFwLess\components\swoole\coresource;

use Swoole\Coroutine;

class CoroutineRes
{
    public static $coroutineRes = [];

    public static function register($res, $cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();
        self::$coroutineRes[$cid][get_class($res)] =  $res;
    }

    public static function fetch($className, $cid = null)
    {
        return self::$coroutineRes[$cid ?? Coroutine::getCid()][$className] ?? null;
    }

    public static function release($className, $cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();
        if (isset(self::$coroutineRes[$cid][$className])) {
            unset(self::$coroutineRes[$cid][$className]);
        }
    }

    public static function releaseAll($cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();
        if (isset(self::$coroutineRes[$cid])) {
            unset(self::$coroutineRes[$cid]);
        }
    }
}
