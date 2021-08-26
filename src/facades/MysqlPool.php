<?php

namespace SwFwLess\facades;

use SwFwLess\components\mysql\MysqlWrapper;

/**
 * Class MysqlPool
 *
 * @method static MysqlWrapper pick($connectionName = null, $callback = null)
 * @method static release($pdo)
 * @method static MysqlWrapper getConnect($needRelease = true, $connectionName = null)
 * @method static int countPool()
 * @package SwFwLess\facades
 */
class MysqlPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\mysql\MysqlPool::create();
    }
}
