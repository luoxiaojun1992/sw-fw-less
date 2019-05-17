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

    /** @var QueryInterface|QueryFactory */
    private $auraQuery;

    private $db;

    private $connectionName;

    private $needRelease = true;

    private $lastInsertId;

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
            $pdoStatement = $pdo->prepare($this->auraQuery->getStatement());
            if ($pdoStatement) {
                $result = $pdoStatement->execute($this->auraQuery->getBindValues());
                switch ($mode) {
                    case 0:
                        return $result ? $pdoStatement->fetch(\PDO::FETCH_ASSOC) : [];
                    case 1:
                        return $result ? $pdoStatement->fetchAll(\PDO::FETCH_ASSOC) : [];
                    case 2:
                        $res = $result ? $pdoStatement->rowCount() : 0;
                        $this->setLastInsetId($pdo->lastInsertId());
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
     * @param $id
     * @return $this
     */
    private function setLastInsetId($id)
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
}
