<?php

class RedisPoolTest extends \PHPUnit\Framework\TestCase
{
    protected function getTestRedisPool($redisConfig = null)
    {
        require_once __DIR__ . '/../../stubs/components/redis/RedisPool.php';

        return RedisPool::create($redisConfig);
    }

    public function testPickAndRelease()
    {
        $poolSize = 5;

        $redisConfig = [
            'default' => 'default',
            'connections' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'timeout' => 1,
                    'pool_size' => $poolSize,
                    'passwd' => null,
                    'db' => 0,
                    'prefix' => 'sw-fw-less:',
                ],
            ],
            'switch' => 1,
            'pool_change_event' => 1,
            'report_pool_change' => 1,
        ];

        $redisPool = $this->getTestRedisPool($redisConfig);

        $this->assertEquals(
            $poolSize,
            $redisPool->countPool()
        );

        //TODO

        $pdo = $redisPool->pick();

        $this->assertInstanceOf(
            TestRedis::class,
            $pdo
        );
    }
}
