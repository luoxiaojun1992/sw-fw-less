<?php

use Carbon\Carbon;
use SwFwLess\components\Helper;
use SwFwLess\components\mysql\MysqlWrapper;

class TestPDO extends MysqlWrapper
{
    public function reconnect()
    {
        //
    }

    protected function getTestPDOStatement()
    {
        require_once __DIR__ . '/TestPDOStatement.php';

        return new TestPDOStatement();
    }

    public function prepare($statement, array $driver_options = array())
    {
        return $this->getTestPDOStatement();
    }

    public function lastInsertId ($name = null)
    {
        return 1;
    }
}
