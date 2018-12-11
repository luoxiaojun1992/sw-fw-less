<?php

namespace App\components;

class RedisWrapper
{
    /** @var \Redis */
    private $redis;
    private $inTransaction = false;

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
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->redis, $name], $arguments);
        if (strtolower($name) == 'multi') {
            $this->inTransaction = true;
        }
        if (in_array(strtolower($name), ['exec', 'discard'])) {
            $this->inTransaction = false;
        }
        return $result;
    }
}
