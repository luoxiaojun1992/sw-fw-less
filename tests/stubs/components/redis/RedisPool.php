<?php

class RedisPool extends \SwFwLess\components\redis\RedisPool
{
    protected function poolChange($count)
    {
        //
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
}
