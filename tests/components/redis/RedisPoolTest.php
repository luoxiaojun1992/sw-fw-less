<?php

use Mockery as M;

class RedisPoolTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        parent::tearDown();

        require_once __DIR__ . '/../../stubs/components/redis/RedisPool.php';

        RedisPool::clearInstance();
    }

    protected function getTestRedisPool($redisConfig = null)
    {
        require_once __DIR__ . '/../../stubs/components/redis/RedisPool.php';

        return RedisPool::create($redisConfig);
    }

    /**
     * @throws RedisException
     */
    public function testPickAndRelease()
    {
        $mockScheduler = M::mock('alias:' . 'SwFwLess\components\swoole\Scheduler');
        $mockScheduler->shouldReceive('withoutPreemptive')
            ->with(M::type('callable'))
            ->andReturnUsing(function ($arg) {
                return call_user_func($arg);
            });

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

        /** @var TestRedis $redis */
        $redis = $redisPool->pick();

        $this->assertInstanceOf(
            TestRedis::class,
            $redis
        );

        $this->assertTrue(
            $redis->isNeedRelease()
        );

        $this->assertEquals(
            $poolSize - 1,
            $redisPool->countPool()
        );

        $redis->setMockResponseArr(['OK']);
        $redisPool->release($redis);

        $this->assertEquals(
            $poolSize,
            $redisPool->countPool()
        );

        for ($i = 0; $i < $poolSize; ++$i) {
            $redisPool->pick();
        }

        $this->assertEquals(
            0,
            $redisPool->countPool()
        );

        $redis = $redisPool->pick();

        $this->assertFalse($redis->isNeedRelease());

        $redis->setMockResponseArr(['OK']);
        $redisPool->release($redis);

        $this->assertEquals(
            0,
            $redisPool->countPool()
        );
    }
}
