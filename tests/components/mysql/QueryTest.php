<?php

use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\QueryInterface;
use SwFwLess\components\mysql\ModelQuery;

class QueryTest extends \PHPUnit\Framework\TestCase
{
    protected function getQuery()
    {
        require_once __DIR__ . '/../../stubs/components/mysql/Query.php';

        return Query::create();
    }

    protected function getTestPDO()
    {
        require_once __DIR__ . '/../../stubs/runtime/php/TestPDO.php';

        return new TestPDO();
    }

    public function testWrite()
    {
        $mockData = [
            ['id' => 1, 'name' => 'Foo'],
        ];

        //Insert

        /** @var Query|QueryInterface|InsertInterface $query */
        $query = $this->getQuery()->newInsert();
        $query->col('name')->bindValue(':name', 'Foo');
        $query->setMockData($mockData);
        $result = $query->write($this->getTestPDO()->setMockData($mockData));
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $query->getLastInsertId());

        //Update


    }
}
