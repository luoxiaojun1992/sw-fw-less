<?php

namespace SwFwLess\components\redis;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use SwFwLess\components\Helper;
use SwFwLess\facades\RedisPool;
use Cake\Event\Event;

/**
 * Class RedisWrapper
 * @package SwFwLess\components\redis
 * @mixin \Redis
 */
class RedisWrapper
{
    const EVENT_EXECUTING = 'redis.executing';
    const EVENT_EXECUTED = 'redis.executed';

    /** @var \Redis */
    private $redis;
    private $inTransaction = false;
    private $needRelease = true;
    private $connectionName;
    private $idleTimeout = 500; //seconds
    private $lastConnectedAt;
    private $lastActivityAt;

    /**
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param $redis
     * @return $this
     */
    public function setRedis($redis)
    {
        $this->redis = $redis;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedRelease()
    {
        return $this->needRelease;
    }

    /**
     * @param bool $needRelease
     * @return $this
     */
    public function setNeedRelease($needRelease)
    {
        $this->needRelease = $needRelease;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @param mixed $connectionName
     * @return $this
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @return int
     */
    public function getIdleTimeout(): int
    {
        return $this->idleTimeout;
    }

    /**
     * @param int $idleTimeout
     * @return $this
     */
    public function setIdleTimeout(int $idleTimeout)
    {
        $this->idleTimeout = $idleTimeout;
        return $this;
    }

    /**
     * @return null|CarbonInterface
     */
    public function getLastConnectedAt()
    {
        return $this->lastConnectedAt;
    }

    /**
     * @param null|CarbonInterface $lastConnectedAt
     * @return $this
     */
    public function setLastConnectedAt($lastConnectedAt = null)
    {
        $this->lastConnectedAt = ($lastConnectedAt ?: Carbon::now());
        return $this;
    }

    /**
     * @return null|CarbonInterface
     */
    public function getLastActivityAt()
    {
        return $this->lastActivityAt;
    }

    /**
     * @param null|CarbonInterface $lastActivityAt
     * @return $this
     */
    public function setLastActivityAt($lastActivityAt = null)
    {
        $this->lastActivityAt = ($lastActivityAt ?: Carbon::now());
        return $this;
    }

    /**
     * @return bool
     */
    public function exceedIdleTimeout()
    {
        return (Carbon::now()->diffInSeconds($this->getLastActivityAt())) > ($this->getIdleTimeout());
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    private function callRedis($name, $arguments)
    {
        $result = $this->callRedisWithEvents(function () use ($name, $arguments) {
            return call_user_func_array([$this->redis, $name], $arguments);
        });
        $lowerCaseName = strtolower($name);
        if ($lowerCaseName == 'multi') {
            $this->inTransaction = true;
        }
        if (in_array($lowerCaseName, ['exec', 'discard'])) {
            $this->inTransaction = false;
        }
        return $result;
    }

    /**
     * @param $executor
     * @return mixed
     */
    private function callRedisWithEvents($executor)
    {
        \SwFwLess\components\functions\event(new Event(
            static::EVENT_EXECUTING,
            null,
            [
                'connection' => $this->getConnectionName()
            ]
        ));

        $executingAt = microtime(true) * 1000;

        $result = call_user_func($executor);

        \SwFwLess\components\functions\event(new Event(
            static::EVENT_EXECUTED,
            null,
            [
                'connection' => $this->getConnectionName(),
                'time' => microtime(true) * 1000 - $executingAt,
            ]
        ));

        return $result;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \RedisException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->redis, $name)) {
            try {
                return $this->callRedis($name, $arguments);
            } catch (\RedisException $e) {
                if (!$this->inTransaction() && Helper::causedByLostConnection($e)) {
                    $this->handleCommandException($e);
                    return $this->callRedis($name, $arguments);
                }

                throw $e;
            }
        }

        return null;
    }

    /**
     * @param \RedisException $e
     */
    private function handleCommandException(\RedisException $e)
    {
        //todo retry argument
        if (!$this->inTransaction() && Helper::causedByLostConnection($e)) {
            $this->setRedis(RedisPool::getConnect(false, $this->getConnectionName())->getRedis());
        }
    }
}
