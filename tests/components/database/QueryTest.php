<?php

namespace SwFwLessTest\components\database;

use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\QueryInterface;
use SwFwLess\components\Config;
use SwFwLessTest\stubs\components\database\ConnectionPool;
use SwFwLessTest\stubs\components\database\PDOWrapper;
use SwFwLessTest\stubs\components\database\Query;

class QueryTest extends \PHPUnit\Framework\TestCase
{
    protected function beforeTest()
    {
        parent::setUp();
        Config::initByArr([
            'database' => [
                'default' => 'default',
                'connections' => [
                    'default' => [
                        'driver' => 'mysql',
                        'dsn' => 'mysql:dbname=sw_test;host=127.0.0.1',
                        'username' => 'root',
                        'passwd' => null,
                        'options' => [
                            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
                            \PDO::ATTR_STRINGIFY_FETCHES => false,
                            \PDO::ATTR_EMULATE_PREPARES => false,
                            \PDO::ATTR_PERSISTENT => false,
                        ],
                        'pool_size' => 1,
                        'table_prefix' => '',
                    ],
                ],
                'switch' => 1,
                'pool_change_event' => 0,
                'report_pool_change' => 0,
            ]
        ]);

        ConnectionPool::create(Config::get('database'));
    }

    protected function afterTest()
    {
        parent::tearDown();
        ConnectionPool::clearInstance();
        Config::clear();
    }

    protected function getQuery()
    {
        require_once __DIR__ . '/../../stubs/components/database/Query.php';

        return Query::create();
    }

    protected function getTestPDOWrapper()
    {
        require_once __DIR__ . '/../../stubs/components/database/PDOWrapper.php';

        return new PDOWrapper();
    }

    public function testWrite()
    {
        $this->beforeTest();

        //Insert
        $mockData = [
            ['id' => 1, 'name' => 'Foo'],
        ];
        /** @var Query|QueryInterface|InsertInterface $query */
        $query = $this->getQuery()->newInsert();
        $query->col('name')->bindValue(':name', 'Foo');
        $query->setMockData($mockData);
        $testPDOWrapper = $this->getTestPDOWrapper();
        $result = $query->write($testPDOWrapper->setMockData($mockData));
        $this->assertEquals(1, $result);
        $this->assertEquals(1, $query->getLastInsertId());
        $this->assertIsString($testPDOWrapper->getTestStatement());
        $testPDOStatement = $testPDOWrapper->getTestLastPdoStatement();
        $this->assertTrue(
            $testPDOStatement->getTestBindingValues() ===
            [
                ':name' => [
                    'value' => 'Foo',
                    'data_type' => \PDO::PARAM_STR,
                ]
            ]
        );

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
        $testPDOWrapper = $this->getTestPDOWrapper();
        $result = $query->write($testPDOWrapper->setMockData($mockData));
        $this->assertEquals(1, $result);
        $this->assertIsString($testPDOWrapper->getTestStatement());
        $testPDOStatement = $testPDOWrapper->getTestLastPdoStatement();
        $this->assertTrue(
            $testPDOStatement->getTestBindingValues() ===
            [
                ':primaryValue' => [
                    'value' => 1,
                    'data_type' => \PDO::PARAM_INT,
                ],
                ':name' => [
                    'value' => 'Bar',
                    'data_type' => \PDO::PARAM_STR,
                ]
            ]
        );

        //Delete
        $mockData = [
            ['id' => 1, 'name' => 'Bar'],
        ];
        /** @var Query|QueryInterface|\Aura\SqlQuery\Common\DeleteInterface $query */
        $query = $this->getQuery()->newDelete();
        $query->where("`id` = :primaryValue");
        $query->bindValue(':primaryValue', 1);
        $testPDOWrapper = $this->getTestPDOWrapper();
        $query->write($testPDOWrapper->setMockData($mockData));
        $this->assertEquals(1, $result);
        $this->assertIsString($testPDOWrapper->getTestStatement());
        $testPDOStatement = $testPDOWrapper->getTestLastPdoStatement();
        $this->assertTrue(
            $testPDOStatement->getTestBindingValues() ===
            [
                ':primaryValue' => [
                    'value' => 1,
                    'data_type' => \PDO::PARAM_INT,
                ],
            ]
        );

        $this->afterTest();
    }

    public function testGetOrFirst()
    {
        $this->beforeTest();

        //First
        $mockData = [
            ['id' => 1, 'name' => 'Foo'],
        ];
        /** @var Query|QueryInterface|\Aura\SqlQuery\Common\SelectInterface $query */
        $query = $this->getQuery()->newSelect();
        $query->cols(['id', 'name']);
        $query->where('`id` = :primaryValue');
        $query->bindValue(':primaryValue', 1);
        $testPDOWrapper = $this->getTestPDOWrapper();
        $result = $query->first($testPDOWrapper->setMockData($mockData));
        $this->assertEquals(
            $mockData[0],
            $result
        );
        $this->assertIsString($testPDOWrapper->getTestStatement());
        $testPDOStatement = $testPDOWrapper->getTestLastPdoStatement();
        $this->assertTrue(
            $testPDOStatement->getTestBindingValues() ===
            [
                ':primaryValue' => [
                    'value' => 1,
                    'data_type' => \PDO::PARAM_INT,
                ]
            ]
        );

        //Get
        $mockData = [
            ['id' => 1, 'name' => 'Foo'],
        ];
        /** @var Query|QueryInterface|\Aura\SqlQuery\Common\SelectInterface $query */
        $query = $this->getQuery()->newSelect();
        $query->cols(['id', 'name']);
        $query->where('`id` = :primaryValue');
        $query->bindValue(':primaryValue', 1);
        $testPDOWrapper = $this->getTestPDOWrapper();
        $result = $query->get($testPDOWrapper->setMockData($mockData));
        $this->assertEquals(
            $mockData,
            $result
        );
        $this->assertIsString($testPDOWrapper->getTestStatement());
        $testPDOStatement = $testPDOWrapper->getTestLastPdoStatement();
        $this->assertTrue(
            $testPDOStatement->getTestBindingValues() ===
            [
                ':primaryValue' => [
                    'value' => 1,
                    'data_type' => \PDO::PARAM_INT,
                ]
            ]
        );

        $this->afterTest();
    }
}
