<?php

namespace App\components;

use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;

class Query
{
    /** @var QueryInterface|QueryFactory */
    private $auraQuery;

    private $db;

    private $needRelease = true;

    /**
     * @param $db
     * @return QueryFactory|Query
     */
    public static function create($db)
    {
        return new self($db);
    }

    /**
     * @return QueryFactory|Query
     */
    public static function createMysql()
    {
        return self::create('mysql');
    }

    public function __construct($db)
    {
        $this->db = $db;
        $this->auraQuery = new QueryFactory($db);
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $result = call_user_func_array([$this->auraQuery, $name], $arguments);
        if (is_object($result)) {
            $this->auraQuery = $result;
        }
        return $this;
    }

    /**
     * @param \PDO $pdo
     * @return array
     */
    private function doMysqlExecute($pdo)
    {
        $pdoStatement = $pdo->prepare($this->auraQuery->getStatement());
        if ($pdoStatement) {
            if ($pdoStatement->execute($this->auraQuery->getBindValues())) {
                $result = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
                return $result;
            }
        }

        return [];
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    private function mysqlExecute($pdo = null)
    {
        if ($pdo) {
            $this->needRelease = false;
        }

        $doMethod = 'doMysqlExecute';
        if (!method_exists($this, $doMethod)) {
            $this->releasePDO($pdo);
            return [];
        }

        try {
            $pdo = $pdo ?: Mysql::create()->pick();
            $result = call_user_func_array([$this, $doMethod], [$pdo]);
            $this->releasePDO($pdo);
            return $result;
        } catch (\PDOException $e) {
            if ($pdo && !$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
                $pdo = $this->handleMysqlExecuteException($pdo, $e);
                $result = call_user_func_array([$this, $doMethod], [$pdo]);
                $this->releasePDO($pdo);
                return $result;
            }

            $this->releasePDO($pdo);

            throw $e;
        }
    }

    /**
     * @param \PDO $pdo
     */
    private function releasePDO($pdo)
    {
        if ($this->needRelease) {
            Mysql::create()->release($pdo);
        }
    }

    /**
     * @param null $pdo
     * @return array
     */
    public function execute($pdo = null)
    {
        $method = $this->db . 'Execute';
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], [$pdo]);
        }

        return [];
    }

    /**
     * @param \PDO $pdo
     * @param \PDOException $e
     * @return \PDO
     */
    private function handleMysqlExecuteException($pdo, \PDOException $e)
    {
        if (!$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
            $pdo = Mysql::create()->getConnect();
        }

        return $pdo;
    }
}
