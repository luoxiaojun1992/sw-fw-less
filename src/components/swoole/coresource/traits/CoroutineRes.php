<?php

namespace SwFwLess\components\swoole\coresource\traits;

trait CoroutineRes
{
    public static function register($res, $cid = null)
    {
        \SwFwLess\components\swoole\coresource\CoroutineRes::register($res, $cid);
    }

    public static function fetch($cid = null)
    {
        return \SwFwLess\components\swoole\coresource\CoroutineRes::fetch(static::class, $cid);
    }

    public static function release($cid = null, $releaseToPool = true)
    {
        \SwFwLess\components\swoole\coresource\CoroutineRes::release(
            static::class, $cid, $releaseToPool
        );
    }
}
