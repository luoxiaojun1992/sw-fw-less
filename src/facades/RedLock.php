<?php

namespace SwFwLess\facades;

/**
 * Class RedisLock
 *
 * @method static bool lock($key, $ttl = 0, $guard = false, $callback = null)
 * @method static bool unlock($key)
 * @method static flushAll()
 * @package SwFwLess\facades
 */
class RedLock extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\redis\RedLock::create();
    }
}
