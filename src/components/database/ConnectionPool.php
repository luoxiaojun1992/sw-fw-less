<?php

namespace SwFwLess\components\database;

use SwFwLess\components\Helper;
use Cake\Event\Event as CakeEvent;
use SwFwLess\components\swoole\Scheduler;

class ConnectionPool
{
    const EVENT_PDO_POOL_CHANGE = 'pdo.pool.change';

    protected static $instance;

    /** @var PDOWrapper[][] */
    private $connectionPool = [];

    protected $config = [];

    /** @var Connector */
    protected $connector;

    public static function clearInstance()
    {
        static::$instance = null;
    }

    public static function create($dbConfig = null)
    {
        if (static::$instance instanceof static) {
            return static::$instance;
        }

        if (is_array($dbConfig) && !empty($dbConfig['switch'])) {
            return static::$instance = new static($dbConfig, new Connector());
        } else {
            return null;
        }
    }

    /**
     * ConnectionPool constructor.
     * @param array $dbConfig
     * @param Connector $connector
     */
    public function __construct($dbConfig, $connector)
    {
        $this->config = $dbConfig;
        $this->connector = $connector;

        foreach ($this->config['connections'] as $connectionName => $dbConnection) {
            for ($i = 0; $i < $dbConnection['pool_size']; ++$i) {
                if (!is_null($connection = $this->getConnect(true, $connectionName))) {
                    $this->connectionPool[$connectionName][] = $connection;
                }
            }

            if ($this->config['pool_change_event']) {
                $this->poolChange($dbConnection['pool_size']);
            }
        }
    }

    protected function poolChange($count)
    {
        \SwFwLess\components\functions\event(
            new CakeEvent(static::EVENT_PDO_POOL_CHANGE,
                null,
                ['count' => $count]
            )
        );
    }

    /**
     * @param string|null $connectionName
     * @param callable $callback
     * @return PDOWrapper mixed
     * @throws \Throwable
     */
    public function pick($connectionName = null, $callback = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->connectionPool[$connectionName])) {
            return null;
        }
        /** @var PDOWrapper $pdo */
        $pdo = Scheduler::withoutPreemptive(function () use ($connectionName) {
            return array_pop($this->connectionPool[$connectionName]);
        });
        if (!$pdo) {
            $pdo = $this->getConnect(false, $connectionName);
        } else {
            if ($pdo->exceedIdleTimeout()) {
                $pdo->reconnect();
            }

            if ($this->config['pool_change_event']) {
                $this->poolChange(-1);
            }
        }

        if (!is_null($callback)) {
            try {
                return call_user_func($callback, $pdo);
            } catch (\Throwable $e) {
                throw $e;
            } finally {
                $this->release($pdo);
            }
        }

        return $pdo;
    }

    /**
     * @param PDOWrapper|\PDO $pdo
     */
    public function release($pdo)
    {
        if ($pdo) {
            if ($pdo->inTransaction()) {
                try {
                    $pdo->rollBack();
                } catch (\PDOException $rollbackException) {
                    $this->handleRollbackException($pdo, $rollbackException);
                }
            }
            if ($pdo->isNeedRelease()) {
                if ($pdo->exceedMaxBigQueryTimes()) {
                    $pdo->reconnect();
                }

                $pdo->setRetry(false);

                Scheduler::withoutPreemptive(function () use ($pdo) {
                    $this->connectionPool[$pdo->getConnectionName()][] = $pdo;
                });
                if ($this->config['pool_change_event']) {
                    $this->poolChange(1);
                }
            }
        }
    }

    /**
     * @param bool $needRelease
     * @param string|null $connectionName
     * @return PDOWrapper
     */
    public function getConnect($needRelease = true, $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->config['connections'][$connectionName])) {
            return null;
        }

        $connection = $this->connector->connect(
            $this->config['connections'][$connectionName]
        );
        return $connection->setNeedRelease($needRelease)
            ->setConnectionName($connectionName)
            ->setConnectionPool($this);
    }

    /**
     * @param PDOWrapper $pdo
     * @param \PDOException $e
     */
    private function handleRollbackException($pdo, \PDOException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            if ($pdo->isNeedRelease()) {
                $pdo->reconnect();
            }
        } else {
            throw $e;
        }
    }

    /**
     * @return int
     */
    public function countPool()
    {
        $sum = 0;
        foreach ($this->connectionPool as $connections) {
            $sum += count($connections);
        }
        return $sum;
    }
}
