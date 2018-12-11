<?php

namespace App\components;

class MysqlPool
{
    private static $instance;

    /** @var \PDO[] */
    private $pdoPool = [];

    private $dsn;
    private $username;
    private $passwd;
    private $options = [];
    private $poolSize;

    public static function create($dsn = '', $username = '', $passwd = null, $options = [], $poolSize = 100)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($dsn, $username, $passwd, $options, $poolSize);
    }

    /**
     * Mysql constructor.
     * @param $dsn
     * @param $username
     * @param $passwd
     * @param $options
     * @param $poolSize
     */
    public function __construct($dsn, $username, $passwd, $options, $poolSize)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->options = $options;
        $this->poolSize = $poolSize;

        for ($i = 0; $i < $poolSize; ++$i) {
            $this->pdoPool[] = $this->getConnect();
        }
    }

    /**
     * @return \PDO mixed
     */
    public function pick()
    {
        return array_pop($this->pdoPool);
    }

    /**
     * @param \PDO $pdo
     */
    public function release($pdo)
    {
        if ($pdo) {
            if ($pdo->inTransaction()) {
                try {
                    $pdo->rollBack();
                } catch (\PDOException $rollbackException) {
                    $pdo = $this->handleRollbackException($pdo, $rollbackException);
                }
            }
            $this->pdoPool[] = $pdo;
        }
    }

    public function __destruct()
    {
        foreach ($this->pdoPool as $i => $pdo) {
            unset($this->pdoPool[$i]);
        }
    }

    /**
     * @return \PDO
     */
    public function getConnect()
    {
        return new \PDO($this->dsn, $this->username, $this->passwd, $this->options);
    }

    /**
     * @param \PDO $pdo
     * @param \PDOException $e
     * @return \PDO
     */
    private function handleRollbackException($pdo, \PDOException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $pdo = $this->getConnect();
        }

        return $pdo;
    }
}
