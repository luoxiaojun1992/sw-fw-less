<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\Helper;

class MysqlWrapper
{
    /** @var \PDO */
    private $pdo;
    private $needRelease = true;
    private $connectionName;

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
                if (!$this->pdo->inTransaction() && Helper::causedByLostConnection($e)) {
                    $this->handleMysqlExecuteException($e);
                    return $this->callPdo($name, $arguments);
                }

                throw $e;
            }
        }

        return null;
    }

    /**
     * @param \PDOException $e
     */
    public function handleMysqlExecuteException(\PDOException $e)
    {
        if (!$this->pdo->inTransaction() && Helper::causedByLostConnection($e)) {
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
}
