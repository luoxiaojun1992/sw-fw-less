<?php

namespace App\components;

use App\facades\MysqlPool;
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

    private $needRelease = true;

    private $lastInsertId;

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
     * @param MysqlWrapper|\PDO $pdo
     * @param int $mode
     * @return mixed
     */
    private function doMysqlExecute($pdo, $mode = 0)
    {
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
                    $this->lastInsertId = $pdo->lastInsertId();
                    return $res;
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

        $doMethod = 'doMysqlExecute';

        try {
            $pdo = $pdo ?: MysqlPool::pick();
            return call_user_func_array([$this, $doMethod], [$pdo, $mode]);
        } catch (\PDOException $e) {
            if ($pdo && !$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
                $pdo = $this->handleMysqlExecuteException($pdo, $e);
                return call_user_func_array([$this, $doMethod], [$pdo, $mode]);
            }

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
                'connection' => 'default', //todo multi connections
                'mode' => $mode,
            ]
        ));

        $queryBeginAt = microtime(true) * 1000;

        $result = call_user_func($executor);

        event(new Event(
            static::EVENT_EXECUTED,
            null,
            [
                'db' => $this->db,
                'connection' => 'default', //todo multi connections
                'mode' => $mode,
                'time' => microtime(true) * 1000 - $queryBeginAt,
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
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->getLastInsertId();
    }

    /**
     * @param MysqlWrapper|\PDO $pdo
     * @param \PDOException $e
     * @return MysqlWrapper
     */
    private function handleMysqlExecuteException($pdo, \PDOException $e)
    {
        if (!$pdo->inTransaction() && Helper::causedByLostConnection($e)) {
            $pdo = MysqlPool::getConnect();
        }

        return $pdo;
    }
}
