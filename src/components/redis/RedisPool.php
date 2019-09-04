<?php

namespace SwFwLess\components\redis;

use SwFwLess\components\Helper;
use Cake\Event\Event as CakeEvent;
use SwFwLess\components\pool\AbstractPool;

class RedisPool extends AbstractPool
{
    const EVENT_REDIS_POOL_CHANGE = 'redis.pool.change';

    private static $instance;

    /** @var RedisWrapper[][]|\Redis[][] */
    private $redisPool = [];

    private $config = [];

    /**
     * @param array $redisConfig
     * @return RedisPool|null
     */
    public static function create($redisConfig = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (is_array($redisConfig) && !empty($redisConfig['switch'])) {
            return self::$instance = new self($redisConfig);
        } else {
            return null;
        }
    }

    /**
     * Redis constructor.
     * @param array $redisConfig
     */
    public function __construct($redisConfig)
    {
        $this->config = $redisConfig;

        foreach ($redisConfig['connections'] as $connectionName => $redisConnection) {
            for ($i = 0; $i < $redisConnection['pool_size']; ++$i) {
                if (!is_null($connection = $this->getConnect(true, $connectionName))) {
                    $this->redisPool[$connectionName][] = $connection;
                }
            }

            if ($redisConfig['pool_change_event']) {
                event(
                    new CakeEvent(static::EVENT_REDIS_POOL_CHANGE,
                        null,
                        ['count' => $redisConnection['pool_size']]
                    )
                );
            }
        }

        RedisStreamWrapper::register();
    }

    /**
     * @param string $connectionName
     * @return RedisWrapper mixed
     */
    public function pick($connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->redisPool[$connectionName])) {
            return null;
        }
        $redis = $this->pickFromPool($this->redisPool[$connectionName]);
        if (!$redis) {
            $redis = $this->getConnect(false, $connectionName);
        } else {
            if ($this->config['pool_change_event']) {
                event(
                    new CakeEvent(static::EVENT_REDIS_POOL_CHANGE,
                        null,
                        ['count' => -1]
                    )
                );
            }
        }

        return $redis;
    }

    /**
     * @param RedisWrapper|\Redis $redis
     * @throws \RedisException
     */
    public function release($redis)
    {
        if ($redis) {
            if ($redis->inTransaction()) {
                try {
                    $redis->discard();
                } catch (\RedisException $e) {
                    $redis = $this->handleRollbackException($redis, $e);
                }
            }
            if ($redis->isNeedRelease()) {
                $this->redisPool[$redis->getConnectionName()][] = $redis;
                if ($this->config['pool_change_event']) {
                    event(
                        new CakeEvent(static::EVENT_REDIS_POOL_CHANGE,
                            null,
                            ['count' => 1]
                        )
                    );
                }
            }
        }
    }

    public function __destruct()
    {
        foreach ($this->redisPool as $connectionName => $connections) {
            foreach ($connections as $connection) {
                $connection->close();
            }
        }
    }

    /**
     * @param bool $needRelease
     * @param string $connectionName
     * @return RedisWrapper
     */
    public function getConnect($needRelease = true, $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->config['connections'][$connectionName])) {
            return null;
        }

        $redis = new \Redis();
        $redis->connect(
            $this->config['connections'][$connectionName]['host'],
            $this->config['connections'][$connectionName]['port'],
            $this->config['connections'][$connectionName]['timeout']
        );
        if ($this->config['connections'][$connectionName]['passwd']) {
            $redis->auth($this->config['connections'][$connectionName]['passwd']);
        }
        $redis->setOption(\Redis::OPT_PREFIX, $this->config['connections'][$connectionName]['prefix']);
        $redis->select($this->config['connections'][$connectionName]['db']);
        return (new RedisWrapper())->setRedis($redis)
            ->setNeedRelease($needRelease)
            ->setConnectionName($connectionName);
    }

    /**
     * @param RedisWrapper $redis
     * @param \RedisException $e
     * @return RedisWrapper
     * @throws \RedisException
     */
    private function handleRollbackException($redis, \RedisException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            if ($redis->isNeedRelease()) {
                $redis = $this->getConnect(true, $redis->getConnectionName());
            }
        } else {
            throw $e;
        }

        return $redis;
    }

    /**
     * @return int
     */
    public function countPool()
    {
        $sum = 0;
        foreach ($this->redisPool as $connectionName => $connections) {
            $sum += count($connections);
        }
        return $sum;
    }
}
