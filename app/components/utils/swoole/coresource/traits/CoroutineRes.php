<?php

namespace App\components\utils\swoole\coresource\traits;

trait CoroutineRes
{
    public static $coroutineRes = [];

    public static function register($res, $cid = null)
    {
        \App\components\utils\swoole\coresource\CoroutineRes::register($res, $cid);
    }

    public static function fetch($cid = null)
    {
        return \App\components\utils\swoole\coresource\CoroutineRes::fetch(static::class, $cid);
    }

    public static function release($cid = null)
    {
        \App\components\utils\swoole\coresource\CoroutineRes::release(static::class, $cid);
    }
}
