<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\Helper;
use SwFwLess\components\mysql\traits\MysqlTransaction;

class MysqlWrapper
{
    use MysqlTransaction;

    const MAX_BIG_QUERY_TIMES = 1000000;

    /** @var \PDO */
    private $pdo;
    private $needRelease = true;
    private $connectionName;
    private $retry = false;

    public $bigQueryTimes = 0;

    /**
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * @param $pdo
     * @return $this
     */
    public function setPDO($pdo)
    {
        $this->pdo = $pdo;
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
    public function isRetry(): bool
    {
        return $this->retry;
    }

    /**
     * @param bool $retry
     * @return $this
     */
    public function setRetry(bool $retry)
    {
        $this->retry = $retry;
        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    private function callPdo($name, $arguments)
    {
        return call_user_func_array([$this->pdo, $name], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->pdo, $name)) {
            try {
                return $this->callPdo($name, $arguments);
            } catch (\PDOException $e) {
                if (Helper::causedByLostConnection($e)) {
                    $this->handleExecuteException($e);
                    if (($this->isRetry()) && (!$this->inTransaction())) {
                        return $this->callPdo($name, $arguments);
                    }
                }

                throw $e;
            }
        }

        return null;
    }

    /**
     * @param \PDOException $e
     */
    public function handleExecuteException(\PDOException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $this->reconnect();
        }
    }

    public function reconnect()
    {
        $this->setPDO(\SwFwLess\facades\MysqlPool::getConnect(false, $this->getConnectionName())->getPDO())
            ->setBigQueryTimes(0);
    }

    /**
     * @return int
     */
    public function getBigQueryTimes(): int
    {
        return $this->bigQueryTimes;
    }

    /**
     * @param int $times
     * @return $this
     */
    public function incrBigQueryTimes(int $times = 1)
    {
        $this->bigQueryTimes += $times;
        return $this;
    }

    /**
     * @param int $times
     * @return $this
     */
    public function setBigQueryTimes(int $times)
    {
        $this->bigQueryTimes = $times;
        return $this;
    }

    /**
     * @return bool
     */
    public function exceedMaxBigQueryTimes()
    {
        return $this->getBigQueryTimes() > self::MAX_BIG_QUERY_TIMES;
    }
}
