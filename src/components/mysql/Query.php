<?php

namespace SwFwLess\components\mysql;

use SwFwLess\facades\MysqlPool;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;
use Cake\Event\Event;

class Query
{
    const EVENT_EXECUTING = 'query.executing';
    const EVENT_EXECUTED = 'query.executed';

    const QUERY_TYPE_FETCH = 0;
    const QUERY_TYPE_FETCH_ALL = 1;
    const QUERY_TYPE_WRITE = 2;

    /** @var QueryInterface|QueryFactory */
    private $auraQuery;

    private $db;

    private $connectionName;

    private $needRelease = true;

    private $lastInsertId;

    private $sql;

    private $bindValues;

    /**
     * @param string $db
     * @param string $connectionName
     * @return QueryFactory|Query
     */
    public static function create($db = 'mysql', $connectionName = null)
    {
        return new static($db, $connectionName);
    }

    /**
     * @param string $db
     * @param string $connectionName
     * @return \Aura\SqlQuery\Common\SelectInterface
     */
    public static function select($db = 'mysql', $connectionName = null)
    {
        return static::create($db, $connectionName)->newSelect();
    }

    /**
     * @param string $db
     * @param string $connectionName
     * @return \Aura\SqlQuery\Common\UpdateInterface
     */
    public static function update($db = 'mysql', $connectionName = null)
    {
        return static::create($db, $connectionName)->newUpdate();
    }

    /**
     * @param string $db
     * @param string $connectionName
     * @return \Aura\SqlQuery\Common\InsertInterface
     */
    public static function insert($db = 'mysql', $connectionName = null)
    {
        return static::create($db, $connectionName)->newInsert();
    }

    /**
     * @param string $db
     * @param string $connectionName
     * @return \Aura\SqlQuery\Common\DeleteInterface
     */
    public static function delete($db = 'mysql', $connectionName = null)
    {
        return static::create($db, $connectionName)->newDelete();
    }

    public function __construct($db, $connectionName)
    {
        $this->db = $db;

        if (is_null($connectionName)) {
            $connectionName = config($this->db . '.default');
        }
        $this->connectionName = $connectionName;

        $this->auraQuery = new QueryFactory($db);
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|mixed|null
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->auraQuery, $name)) {
            $result = call_user_func_array([$this->auraQuery, $name], $arguments);
            if (is_object($result)) {
                $this->auraQuery = $result;
                return $this;
            } else {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param MysqlWrapper|null|\PDO $pdo
     * @param $mode
     * @return mixed
     */
    private function mysqlExecute($pdo = null, $mode = 0)
    {
        if ($pdo) {
            $this->needRelease = false;
        }

        try {
            /** @var MysqlWrapper|\PDO $pdo $pdo */
            $pdo = $pdo ?: MysqlPool::pick($this->connectionName);

            /** @var \PDOStatement $pdoStatement */
            $pdoStatement = $pdo->prepare($this->setSql($this->auraQuery->getStatement())->getSql());
            if ($pdoStatement) {
                $result = $pdoStatement->execute($this->setBindValues($this->auraQuery->getBindValues())->getBindValues());
                switch ($mode) {
                    case static::QUERY_TYPE_FETCH:
                        return $result ? $pdoStatement->fetch(\PDO::FETCH_ASSOC) : [];
                    case static::QUERY_TYPE_FETCH_ALL:
                        return $result ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
                    case static::QUERY_TYPE_WRITE:
                        $res = $result ? $pdoStatement->rowCount() : 0;
                        $this->setLastInsertId($pdo->lastInsertId());
                        return $res;
                }
            }

            return null;
        } catch (\PDOException $e) {
            throw $e;
        } finally {
            $this->releasePDO($pdo);
        }
    }

    /**
     * @param MysqlWrapper $pdo
     */
    private function releasePDO($pdo)
    {
        if ($this->needRelease) {
            MysqlPool::release($pdo);
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
            return $this->executeWithEvents(function () use ($method, $pdo, $mode) {
                return call_user_func_array([$this, $method], [$pdo, $mode]);
            }, $mode);
        }

        return null;
    }

    /**
     * @param $executor
     * @param $mode
     * @return mixed
     */
    private function executeWithEvents($executor, $mode)
    {
        event(new Event(
            static::EVENT_EXECUTING,
            null,
            [
                'db' => $this->db,
                'connection' => $this->connectionName,
                'mode' => $mode,
            ]
        ));

        $executingAt = microtime(true) * 1000;

        $result = call_user_func($executor);

        event(new Event(
            static::EVENT_EXECUTED,
            null,
            [
                'db' => $this->db,
                'connection' => $this->connectionName,
                'mode' => $mode,
                'time' => microtime(true) * 1000 - $executingAt,
            ]
        ));

        return $result;
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    public function first($pdo = null)
    {
        $this->limit(1);
        return $this->execute($pdo, static::QUERY_TYPE_FETCH);
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    public function get($pdo = null)
    {
        return $this->execute($pdo, static::QUERY_TYPE_FETCH_ALL);
    }

    /**
     * @param null $pdo
     * @return mixed
     */
    public function write($pdo = null)
    {
        return $this->execute($pdo, static::QUERY_TYPE_WRITE);
    }

    /**
     * @param $id
     * @return $this
     */
    private function setLastInsertId($id)
    {
        $this->lastInsertId = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param mixed $sql
     * @return $this
     */
    public function setSql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBindValues()
    {
        return $this->bindValues;
    }

    /**
     * @param mixed $bindValues
     * @return $this
     */
    public function setBindValues($bindValues)
    {
        $this->bindValues = $bindValues;
        return $this;
    }
}
