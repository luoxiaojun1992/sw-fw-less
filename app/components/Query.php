<?php

namespace App\components;

use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;

class Query
{
    /** @var QueryInterface|QueryFactory */
    private $auraQuery;

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
    private function doExecute($pdo)
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
     * @return array
     */
    public function execute($pdo = null)
    {
        try {
            $pdo = $pdo ?: Mysql::create()->pick();
            $result = $this->doExecute($pdo);
            return $result;
        } catch (\PDOException $e) {
            if ($pdo && !$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
                $pdo = $this->handleExecuteException($pdo, $e);
                $result = $this->doExecute($pdo);
                return $result;
            }

            throw $e;
        }
    }

    /**
     * @param \PDO $pdo
     * @param \PDOException $e
     * @return \PDO
     */
    private function handleExecuteException($pdo, \PDOException $e)
    {
        if (!$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
            $pdo = Mysql::create()->getConnect();
        }

        return $pdo;
    }
}
