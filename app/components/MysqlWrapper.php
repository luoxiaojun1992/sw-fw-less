<?php

namespace App\components;

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
    public function __call($name, $arguments)
    {
        if (method_exists($this->pdo, $name)) {
            return call_user_func_array([$this->pdo, $name], $arguments);
        }

        return null;
    }
}
