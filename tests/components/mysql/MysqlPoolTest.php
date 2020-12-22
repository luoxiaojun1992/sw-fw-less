<?php

use Mockery as M;

class MysqlPoolTest extends \PHPUnit\Framework\TestCase
{
    protected function getTestMysqlPool($mysqlConfig = null)
    {
        require_once __DIR__ . '/../../stubs/components/mysql/MysqlPool.php';

        return MysqlPool::create($mysqlConfig);
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

        $mysqlConfig = [
            'default' => \SwFwLess\components\functions\env('MYSQL_DEFAULT', 'default'),
            'connections' => [
                'default' => [
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
            'pool_change_event' => 1,
            'report_pool_change' => 1,
        ];

        $mysqlPool = $this->getTestMysqlPool($mysqlConfig);

        $this->assertEquals(
            $poolSize,
            $mysqlPool->countPool()
        );

        $pdo = $mysqlPool->pick();

        $this->assertInstanceOf(
            TestPDO::class,
            $pdo
        );

        $this->assertTrue(
            $pdo->isNeedRelease()
        );

        $this->assertEquals(
            $poolSize - 1,
            $mysqlPool->countPool()
        );

        $mysqlPool->release($pdo);

        $this->assertEquals(
            $poolSize,
            $mysqlPool->countPool()
        );

        for ($i = 0; $i < $poolSize; ++$i) {
            $mysqlPool->pick();
        }

        $this->assertEquals(
            0,
            $mysqlPool->countPool()
        );

        $pdo = $mysqlPool->pick();

        $this->assertFalse($pdo->isNeedRelease());

        $mysqlPool->release($pdo);

        $this->assertEquals(
            0,
            $mysqlPool->countPool()
        );
    }
}
