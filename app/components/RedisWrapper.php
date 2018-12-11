<?php

namespace App\components;

class RedisWrapper
{
    /** @var \Redis */
    private $redis;
    private $inTransaction = false;
    private $needRelease = true;

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
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    private function callRedis($name, $arguments)
    {
        $result = call_user_func_array([$this->redis, $name], $arguments);
        $lowerName = strtolower($name);
        if ($lowerName == 'multi') {
            $this->inTransaction = true;
        }
        if (in_array($lowerName, ['exec', 'discard'])) {
            $this->inTransaction = false;
        }
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

    /**
     * @param \RedisException $e
     */
    private function handleCommandException(\RedisException $e)
    {
        if (!$this->inTransaction() && Helper::causedByLostConnection($e)) {
            $this->redis = RedisPool::create()->getConnect()->getRedis();
        }
    }
}
