<?php

namespace SwFwLess\facades;

/**
 * Class ObjectPool
 *
 * @method static mixed|null pick($class)
 * @method static release($object)
 * @method static array stats()
 * @package SwFwLess\facades
 */
class ObjectPool extends AbstractFacade
{
    protected static $useCache = true;

    protected static function getAccessor()
    {
        return \SwFwLess\components\pool\ObjectPool::create();
    }
}
