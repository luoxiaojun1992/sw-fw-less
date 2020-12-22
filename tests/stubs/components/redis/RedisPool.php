<?php

class RedisPool extends \SwFwLess\components\redis\RedisPool
{
    protected function poolChange($count)
    {
        //
    }

    protected function getTestRedis()
    {
        require_once __DIR__ . '/../../runtime/php/redis/TestRedis.php';

        return new TestRedis();
    }

    /**
     * @param bool $needRelease
     * @param null $connectionName
     * @return \SwFwLess\components\redis\RedisWrapper|TestRedis|null
     */
    public function getConnect($needRelease = true, $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->config['connections'][$connectionName])) {
            return null;
        }

        return $this->getTestRedis()
            ->setNeedRelease($needRelease)
            ->setConnectionName($connectionName);
    }
}
