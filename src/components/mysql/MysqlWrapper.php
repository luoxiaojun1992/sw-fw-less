<?php

namespace SwFwLess\components\mysql;

use Carbon\Carbon;
use Carbon\CarbonInterface;
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
    private $idleTimeout = 500; //seconds
    private $lastConnectedAt;
    private $lastActivityAt;

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
     * @return mixed
     */
    public function getLastConnectedAt()
    {
        return $this->lastConnectedAt;
    }

    /**
     * @param $lastConnectedAt
     * @return $this
     */
    public function setLastConnectedAt($lastConnectedAt = null)
    {
        $this->lastConnectedAt = ($lastConnectedAt ?: Carbon::now());
        return $this;
    }

    /**
     * @return CarbonInterface
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
    private function callPdo($name, $arguments)
    {
        $this->setLastActivityAt();
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
            ->setLastConnectedAt()
            ->setLastActivityAt()
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
