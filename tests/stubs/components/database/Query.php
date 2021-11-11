<?php

namespace SwFwLessTest\stubs\components\database;

class Query extends \SwFwLess\components\database\Query
{
    protected $mockData = [];

    /**
     * @param array $mockData
     * @return $this
     */
    public function setMockData(array $mockData)
    {
        $this->mockData = $mockData;
        return $this;
    }

    protected function executeWithEvents($executor, $mode)
    {
        return call_user_func($executor);
    }
}
