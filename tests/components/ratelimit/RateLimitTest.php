<?php

class RateLimitTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        RedisPool::clearInstance();
    }

    protected function getTestRedisPool($redisConfig = null)
    {
        require_once __DIR__ . '/../../stubs/components/redis/RedisPool.php';

        return RedisPool::create($redisConfig);
    }

    /**
     * @throws RedisException
     * @throws Throwable
     */
    public function testPass()
    {
        $rateLimitRedisConnection = 'rate_limit';

        $redisConfig = [
            'default' => 'default',
            'connections' => [
                $rateLimitRedisConnection => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'timeout' => 1,
                    'pool_size' => 1,
                    'passwd' => null,
                    'db' => 0,
                    'prefix' => 'sw-fw-less:ratelimit:',
                ],
            ],
            'switch' => 1,
            'pool_change_event' => 1,
            'report_pool_change' => 1,
        ];

        $redisPool = $this->getTestRedisPool($redisConfig);

        $currentThrottle = 1;
        $nextThrottle = $currentThrottle + 1;
        $totalThrottle = 10000;

        /** @var TestRedis $redis */
        $redis = $redisPool->pick($rateLimitRedisConnection);
        $redis->setMockResponseArr([
            $currentThrottle, //current throttle
            $nextThrottle, //next throttle,
            'OK', //discard
        ]);
        $redisPool->release($redis);

        $rateLimitConfig = [
            'connection' => $rateLimitRedisConnection,
        ];

        $rateLimit = \SwFwLess\components\ratelimit\RateLimit::create(
            $redisPool,
            $rateLimitConfig
        );

        $this->assertTrue(
            $rateLimit->pass(
                'test',
                1,
                $totalThrottle,
                $remaining
            )
        );

        $this->assertEquals(
            $totalThrottle - $nextThrottle,
            $remaining
        );
    }
}
