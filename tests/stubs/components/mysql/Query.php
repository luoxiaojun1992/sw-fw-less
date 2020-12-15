<?php

class Query extends \SwFwLess\components\mysql\Query
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

    public static function tablePrefix($db, $connectionName): string
    {
        return 'test_';
    }

    public static function connectionName($db, $connectionName = null): string
    {
        return 'test';
    }

    protected function executeWithEvents($executor, $mode)
    {
        return call_user_func($executor);
    }
}
