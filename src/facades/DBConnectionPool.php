<?php

namespace SwFwLess\facades;

use SwFwLess\components\database\PDOWrapper;

/**
 * Class PDOPool
 *
 * @method static PDOWrapper pick($connectionName = null, $callback = null)
 * @method static release($pdo)
 * @method static PDOWrapper getConnect($needRelease = true, $connectionName = null)
 * @method static int countPool()
 * @package SwFwLess\facades
 */
class DBConnectionPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \SwFwLess\components\database\ConnectionPool::create();
    }
}
