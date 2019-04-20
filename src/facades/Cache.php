<?php

namespace SwFwLess\facades;

/**
 * Class Cache
 *
 * @method static bool set($key, $value, $ttl = 0)
 * @method static bool|string get($key)
 * @package SwFwLess\facades
 */
class Cache extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\cache\Cache::create();
    }
}
