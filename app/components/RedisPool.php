<?php

namespace App\components;

use App\facades\Event;
use Cake\Event\Event as CakeEvent;

class RedisPool
{
    private static $instance;

    /** @var RedisWrapper[][] */
    private $redisPool = [];

    private $defaultConnection = 'default';
    private $connectionConfigs = [];

    /**
     * @param array $redisConfig
     * @return RedisPool|null
     */
    public static function create($redisConfig = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('redis.switch')) {
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
        $this->defaultConnection = $redisConfig['default'];

        foreach ($redisConfig['connections'] as $connectionName => $redisConnection) {
            $this->connectionConfigs[$connectionName]['host'] = $redisConnection['host'];
            $this->connectionConfigs[$connectionName]['port'] = $redisConnection['port'];
            $this->connectionConfigs[$connectionName]['timeout'] = $redisConnection['timeout'];
            $this->connectionConfigs[$connectionName]['pool_size'] = $redisConnection['pool_size'];
            $this->connectionConfigs[$connectionName]['passwd'] = $redisConnection['passwd'];
            $this->connectionConfigs[$connectionName]['db'] = $redisConnection['db'];
            $this->connectionConfigs[$connectionName]['prefix'] = $redisConnection['prefix'];

            for ($i = 0; $i < $redisConnection['pool_size']; ++$i) {
                if (!is_null($connection = $this->getConnect(true, $connectionName))) {
                    $this->redisPool[$connectionName][] = $connection;
                }
            }

            if ($redisConfig['pool_change_event']) {
                Event::dispatch(
                    new CakeEvent('redis:pool:change',
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
            $connectionName = $this->defaultConnection;
        }
        if (!isset($this->redisPool[$connectionName])) {
            return null;
        }
        $redis = array_pop($this->redisPool[$connectionName]);
        if (!$redis) {
            $redis = $this->getConnect(false, $connectionName);
        } else {
            if (config('redis.pool_change_event')) {
                Event::dispatch(
                    new CakeEvent('redis:pool:change',
                        null,
                        ['count' => -1]
                    )
                );
            }
        }

        return $redis;
    }

    /**
     * @param RedisWrapper $redis
     */
    public function release($redis)
    {
        if ($redis) {
            if ($redis->inTransaction()) {
                try {
                    $redis->discard();
                } catch (\RedisException $e) {
                    if ($redis->isNeedRelease()) {
                        $redis = $this->handleRollbackException($redis, $e);
                    }
                }
            }
            if ($redis->isNeedRelease()) {
                $this->redisPool[$redis->getConnectionName()][] = $redis;
                if (Config::get('redis.pool_change_event')) {
                    Event::dispatch(
                        new CakeEvent('redis:pool:change',
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
            $connectionName = $this->defaultConnection;
        }
        if (!isset($this->connectionConfigs[$connectionName])) {
            return null;
        }
        $redis = new \Redis();
        $redis->connect(
            $this->connectionConfigs[$connectionName]['host'],
            $this->connectionConfigs[$connectionName]['port'],
            $this->connectionConfigs[$connectionName]['timeout']
        );
        if ($this->connectionConfigs[$connectionName]['passwd']) {
            $redis->auth($this->connectionConfigs[$connectionName]['passwd']);
        }
        $redis->setOption(\Redis::OPT_PREFIX, $this->connectionConfigs[$connectionName]['prefix']);
        $redis->select($this->connectionConfigs[$connectionName]['db']);
        return (new RedisWrapper())->setRedis($redis)
            ->setNeedRelease($needRelease)
            ->setConnectionName($connectionName);
    }

    /**
     * @param RedisWrapper $redis
     * @param \RedisException $e
     * @return RedisWrapper
     */
    public function handleRollbackException($redis, \RedisException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $redis = $this->getConnect(true, $redis->getConnectionName());
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
