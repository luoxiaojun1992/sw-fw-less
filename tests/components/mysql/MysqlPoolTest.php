<?php

class MysqlPoolTest extends \PHPUnit\Framework\TestCase
{
    protected function getTestMysqlPool($mysqlConfig = null)
    {
        require_once __DIR__ . '/../../stubs/components/mysql/MysqlPool.php';

        return MysqlPool::create($mysqlConfig);
    }

    public function testPickAndRelease()
    {
        //TODO
        $this->assertTrue(true);
    }
}
