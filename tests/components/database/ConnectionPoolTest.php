<?php

namespace SwFwLessTest\components\database;

use Mockery as M;
use SwFwLessTest\stubs\components\database\ConnectionPool;
use SwFwLessTest\stubs\components\database\PDOWrapper;

class ConnectionPoolTest extends \PHPUnit\Framework\TestCase
{
    public function afterTest()
    {
        parent::tearDown();

        require_once __DIR__ . '/../../stubs/components/database/ConnectionPool.php';

        ConnectionPool::clearInstance();
    }

    protected function getTestConnectionPool($dbConfig = null)
    {
        require_once __DIR__ . '/../../stubs/components/database/ConnectionPool.php';

        return ConnectionPool::create($dbConfig);
    }

    public function testPickAndRelease()
    {
        $mockScheduler = M::mock('alias:' . 'SwFwLess\components\swoole\Scheduler');
        $mockScheduler->shouldReceive('withoutPreemptive')
            ->with(M::type('callable'))
            ->andReturnUsing(function ($arg) {
                return call_user_func($arg);
            });

        $poolSize = 5;

        $dbConfig = [
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
                    'pool_size' => $poolSize,
                    'table_prefix' => '',
                ],
            ],
            'switch' => 1,
            'pool_change_event' => 0,
            'report_pool_change' => 0,
        ];

        $connectionPool = $this->getTestConnectionPool($dbConfig);

        $this->assertEquals(
            $poolSize,
            $connectionPool->countPool()
        );

        $pdo = $connectionPool->pick();

        $this->assertInstanceOf(
            PDOWrapper::class,
            $pdo
        );

        $this->assertTrue(
            $pdo->isNeedRelease()
        );

        $this->assertEquals(
            $poolSize - 1,
            $connectionPool->countPool()
        );

        $connectionPool->release($pdo);

        $this->assertEquals(
            $poolSize,
            $connectionPool->countPool()
        );

        for ($i = 0; $i < $poolSize; ++$i) {
            $connectionPool->pick();
        }

        $this->assertEquals(
            0,
            $connectionPool->countPool()
        );

        $pdo = $connectionPool->pick();

        $this->assertFalse($pdo->isNeedRelease());

        $connectionPool->release($pdo);

        $this->assertEquals(
            0,
            $connectionPool->countPool()
        );

        $this->afterTest();
    }
}
