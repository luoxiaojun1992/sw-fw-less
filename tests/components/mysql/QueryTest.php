<?php

use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\QueryInterface;

class QueryTest extends \PHPUnit\Framework\TestCase
{
    protected function getQuery()
    {
        require_once __DIR__ . '/../../stubs/components/mysql/Query.php';

        return Query::create();
    }

    protected function getTestPDO()
    {
        require_once __DIR__ . '/../../stubs/runtime/php/pdo/TestPDO.php';

        return new TestPDO();
    }

    public function testWrite()
    {
        //Insert
        $mockData = [
            ['id' => 1, 'name' => 'Foo'],
        ];
        /** @var Query|QueryInterface|InsertInterface $query */
        $query = $this->getQuery()->newInsert();
        $query->col('name')->bindValue(':name', 'Foo');
        $query->setMockData($mockData);
        $result = $query->write($this->getTestPDO()->setMockData($mockData));
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $query->getLastInsertId());

        //Update
        $mockData = [
            ['id' => 1, 'name' => 'Bar'],
        ];
        /** @var Query|QueryInterface|\Aura\SqlQuery\Common\UpdateInterface $query */
        $query = $this->getQuery()->newUpdate();
        $query->where("`id` = :primaryValue");
        $query->bindValue(':primaryValue', 1);
        $query->col('name')->bindValue(':name', 'Bar');
        $query->setMockData($mockData);
        $result = $query->write($this->getTestPDO()->setMockData($mockData));
        $this->assertEquals(1, $result);
    }
}
