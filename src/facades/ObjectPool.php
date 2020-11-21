<?php

namespace SwFwLess\facades;

/**
 * Class ObjectPool
 *
 * @method static mixed|null pick($class)
 * @method static release($object)
 * @package SwFwLess\facades
 */
class ObjectPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\pool\ObjectPool::create();
    }
}
