<?php

namespace App\facades;

use App\components\mysql\MysqlWrapper;

/**
 * Class MysqlPool
 *
 * @method static MysqlWrapper pick()
 * @method static release($pdo)
 * @method static MysqlWrapper getConnect($needRelease = true)
 * @method static MysqlWrapper handleRollbackException($pdo, \PDOException $e)
 * @method static int countPool()
 * @package App\facades
 */
class MysqlPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\mysql\MysqlPool::create();
    }
}
