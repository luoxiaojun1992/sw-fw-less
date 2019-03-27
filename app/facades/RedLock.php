<?php

namespace App\facades;

/**
 * Class RedisLock
 *
 * @method static bool lock($key, $ttl = 0, $guard = false)
 * @method static bool unlock($key)
 * @method static flushAll()
 * @package App\facades
 */
class RedLock extends AbstractFacade
{
    protected static function getAccessor()
    {
        if (config('redis.switch')) {
            return \App\components\redis\RedLock::create(\App\components\redis\RedisPool::create(), config('red_lock'));
        }

        return null;
    }
}
