<?php

namespace App\facades;

use App\components\hbase\HbaseWrapper;

/**
 * Class HbasePool
 *
 * @method static HbaseWrapper pick()
 * @method static release($connection)
 * @method static HbaseWrapper getConnect($needRelease = true)
 * @method static int countPool()
 * @package App\facades
 */
class HbasePool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\hbase\HbasePool::create();
    }
}
