<?php

namespace App\components\utils\swoole\traits;

use Swoole\Coroutine;

trait CoroutineRes
{
    public static $coroutineRes = [];

    public static function register($res, $cid = null)
    {
        self::$coroutineRes[$cid ?? Coroutine::getCid()] =  $res;
    }

    public static function fetch($cid = null)
    {
        return self::$coroutineRes[$cid ?? Coroutine::getCid()] ?? null;
    }

    public static function release($cid = null)
    {
        $cid = $cid ?? Coroutine::getCid();
        if (isset(self::$coroutineRes[$cid])) {
            unset(self::$coroutineRes[$cid]);
        }
    }
}
