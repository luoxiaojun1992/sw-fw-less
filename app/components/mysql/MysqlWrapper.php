<?php

namespace App\components\mysql;

use App\components\Helper;

class MysqlWrapper
{
    /** @var \PDO */
    private $pdo;
    private $needRelease = true;

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
    private function handleMysqlExecuteException(\PDOException $e)
    {
        if (!$this->pdo->inTransaction() && Helper::causedByLostConnection($e)) {
            $this->setPDO(\App\facades\MysqlPool::getConnect(false)->getPDO());
        }
    }
}
