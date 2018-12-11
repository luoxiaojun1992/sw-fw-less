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
    public static function create($db = 'mysql')
    {
        return new static($db);
    }

    /**
     * @param string $db
     * @return \Aura\SqlQuery\Common\SelectInterface
     */
    public static function select($db = 'mysql')
    {
        return static::create($db)->newSelect();
    }

    /**
     * @param string $db
     * @return \Aura\SqlQuery\Common\UpdateInterface
     */
    public static function update($db = 'mysql')
    {
        return static::create($db)->newUpdate();
    }

    /**
     * @param string $db
     * @return \Aura\SqlQuery\Common\InsertInterface
     */
    public static function insert($db = 'mysql')
    {
        return static::create($db)->newInsert();
    }

    /**
     * @param string $db
     * @return \Aura\SqlQuery\Common\DeleteInterface
     */
    public static function delete($db = 'mysql')
    {
        return static::create($db)->newDelete();
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
        if (method_exists($this->auraQuery, $name)) {
            $result = call_user_func_array([$this->auraQuery, $name], $arguments);
            if (is_object($result)) {
                $this->auraQuery = $result;
            }
        }
        return $this;
    }

    /**
     * @param MysqlWrapper $pdo
     * @param int $mode
     * @return mixed
     */
    private function doMysqlExecute($pdo, $mode = 0)
    {
        $pdoStatement = $pdo->prepare($this->auraQuery->getStatement());
        if ($pdoStatement) {
            if ($result = $pdoStatement->execute($this->auraQuery->getBindValues())) {
                switch ($mode) {
                    case 0:
                        return $pdoStatement->fetch(\PDO::FETCH_ASSOC);
                    case 1:
                        return $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
                    case 2:
                        return $result;
                }
            }
        }

        return null;
    }

    /**
     * @param null $pdo
     * @param $mode
     * @return mixed
     */
    private function mysqlExecute($pdo = null, $mode = 0)
    {
        if ($pdo) {
            $this->needRelease = false;
        }

        $doMethod = 'doMysqlExecute';
        if (!method_exists($this, $doMethod)) {
            $this->releasePDO($pdo);
            return null;
        }

        try {
            $pdo = $pdo ?: MysqlPool::create()->pick();
            $result = call_user_func_array([$this, $doMethod], [$pdo, $mode]);
            $this->releasePDO($pdo);
            return $result;
        } catch (\PDOException $e) {
            if ($pdo && !$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
                $pdo = $this->handleMysqlExecuteException($pdo, $e);
                $result = call_user_func_array([$this, $doMethod], [$pdo, $mode]);
                $this->releasePDO($pdo);
                return $result;
            }

            $this->releasePDO($pdo);

            throw $e;
        }
    }

    /**
     * @param MysqlWrapper $pdo
     */
    private function releasePDO($pdo)
    {
        if ($this->needRelease) {
            MysqlPool::create()->release($pdo);
        }
    }

    /**
     * @param null $pdo
     * @param $mode
     * @return mixed
     */
    public function execute($pdo = null, $mode = 0)
    {
        $method = $this->db . 'Execute';
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], [$pdo, $mode]);
        }

        return null;
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    public function first($pdo = null)
    {
        $this->limit(1);
        return $this->execute($pdo, 0);
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    public function get($pdo = null)
    {
        return $this->execute($pdo, 1);
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    public function write($pdo = null)
    {
        return $this->execute($pdo, 2);
    }

    /**
     * @param MysqlWrapper $pdo
     * @param \PDOException $e
     * @return MysqlWrapper
     */
    private function handleMysqlExecuteException($pdo, \PDOException $e)
    {
        if (!$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
            $pdo = MysqlPool::create()->getConnect();
        }

        return $pdo;
    }
}
