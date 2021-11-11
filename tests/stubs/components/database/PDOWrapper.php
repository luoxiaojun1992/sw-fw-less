<?php

namespace SwFwLessTest\stubs\components\database;

use SwFwLess\components\database\PDOWrapper as basePDOWrapper;

class PDOWrapper extends basePDOWrapper
{
    protected $mockData;

    /**
     * @param $mockData
     * @return $this
     */
    public function setMockData($mockData)
    {
        $this->mockData = $mockData;
        return $this;
    }

    public function reconnect()
    {
        //
    }

    protected function getTestPDOStatement()
    {
        require_once __DIR__ . '/../../runtime/php/pdo/TestPDOStatement.php';

        return (new \TestPDOStatement())->setMockData($this->mockData);
    }

    public function prepare($statement, array $driver_options = array())
    {
        return $this->getTestPDOStatement();
    }

    public function lastInsertId ($name = null)
    {
        return $this->mockData[0][$name];
    }

    public function rollback()
    {
        return true;
    }
}
