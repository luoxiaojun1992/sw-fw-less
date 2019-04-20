<?php

namespace SwFwLess\facades;

use SwFwLess\components\hbase\HbaseWrapper;

/**
 * Class HbasePool
 *
 * @method static HbaseWrapper pick()
 * @method static release($connection)
 * @method static HbaseWrapper getConnect($needRelease = true)
 * @method static int countPool()
 * @package SwFwLess\facades
 */
class HbasePool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\hbase\HbasePool::create();
    }
}
