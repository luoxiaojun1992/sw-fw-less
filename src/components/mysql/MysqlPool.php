<?php

namespace SwFwLess\components\mysql;

use SwFwLess\components\Helper;
use Cake\Event\Event as CakeEvent;
use SwFwLess\components\pool\AbstractPool;

class MysqlPool extends AbstractPool
{
    const EVENT_MYSQL_POOL_CHANGE = 'mysql.pool.change';

    private static $instance;

    /** @var MysqlWrapper[][] */
    private $pdoPool = [];

    private $config = [];

    public static function create($mysqlConfig = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (is_array($mysqlConfig) && !empty($mysqlConfig['switch'])) {
            return self::$instance = new self($mysqlConfig);
        } else {
            return null;
        }
    }

    /**
     * Mysql constructor.
     * @param array $mysqlConfig
     */
    public function __construct($mysqlConfig)
    {
        $this->config = $mysqlConfig;

        foreach ($mysqlConfig['connections'] as $connectionName => $mysqlConnection) {
            for ($i = 0; $i < $mysqlConnection['pool_size']; ++$i) {
                if (!is_null($connection = $this->getConnect(true, $connectionName))) {
                    $this->pdoPool[$connectionName][] = $connection;
                }
            }

            if ($mysqlConfig['pool_change_event']) {
                event(
                    new CakeEvent(static::EVENT_MYSQL_POOL_CHANGE,
                        null,
                        ['count' => $mysqlConnection['pool_size']]
                    )
                );
            }
        }
    }

    /**
     * @param string $connectionName
     * @return MysqlWrapper mixed
     */
    public function pick($connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->pdoPool[$connectionName])) {
            return null;
        }
        $pdo = $this->pickFromPool($this->pdoPool[$connectionName]);
        if (!$pdo) {
            $pdo = $this->getConnect(false, $connectionName);
        } else {
            if ($this->config['pool_change_event']) {
                event(
                    new CakeEvent(static::EVENT_MYSQL_POOL_CHANGE,
                        null,
                        ['count' => -1]
                    )
                );
            }
        }

        return $pdo;
    }

    /**
     * @param MysqlWrapper|\PDO $pdo
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

                $this->pdoPool[$pdo->getConnectionName()][] = $pdo;
                if ($this->config['pool_change_event']) {
                    event(
                        new CakeEvent(static::EVENT_MYSQL_POOL_CHANGE,
                            null,
                            ['count' => 1]
                        )
                    );
                }
            }
        }
    }

    /**
     * @param bool $needRelease
     * @param string $connectionName
     * @return MysqlWrapper
     */
    public function getConnect($needRelease = true, $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->config['connections'][$connectionName])) {
            return null;
        }

        $pdo = new \PDO(
            $this->config['connections'][$connectionName]['dsn'],
            $this->config['connections'][$connectionName]['username'],
            $this->config['connections'][$connectionName]['passwd'],
            $this->config['connections'][$connectionName]['options']
        );
        return (new MysqlWrapper())->setPDO($pdo)
            ->setNeedRelease($needRelease)
            ->setConnectionName($connectionName);
    }

    /**
     * @param MysqlWrapper $pdo
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
        foreach ($this->pdoPool as $connectionName => $connections) {
            $sum += count($connections);
        }
        return $sum;
    }
}
