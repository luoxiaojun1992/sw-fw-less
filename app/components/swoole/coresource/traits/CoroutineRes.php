<?php

namespace App\components\swoole\coresource\traits;

trait CoroutineRes
{
    public static $coroutineRes = [];

    public static function register($res, $cid = null)
    {
        \App\components\swoole\coresource\CoroutineRes::register($res, $cid);
    }

    public static function fetch($cid = null)
    {
        return \App\components\swoole\coresource\CoroutineRes::fetch(static::class, $cid);
    }

    public static function release($cid = null)
    {
        \App\components\swoole\coresource\CoroutineRes::release(static::class, $cid);
    }
}
