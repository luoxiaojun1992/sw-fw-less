<?php

namespace App\facades;

use App\components\MysqlWrapper;

/**
 * Class MysqlPool
 *
 * @method static MysqlWrapper pick()
 * @method static release($pdo)
 * @method static MysqlWrapper getConnect($needRelease = true)
 * @method static MysqlWrapper handleRollbackException($pdo, \PDOException $e)
 * @package App\facades
 */
class MysqlPool extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\MysqlPool::create();
    }
}
