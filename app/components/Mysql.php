<?php

namespace App\components;

class Mysql
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
    }

    /**
     * @return \PDO mixed
     */
    public function pick()
    {
        $pdo = array_pop($this->pdoPool);
        if (!$pdo && count($this->pdoPool) < $this->poolSize) {
            $pdo = $this->getConnect();
        }

        return $pdo;
    }

    /**
     * @param $pdo
     */
    public function release(\PDO $pdo)
    {
        if ($pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            } catch (\PDOException $e) {
                $pdo = $this->handleRollbackException($pdo, $e);
            }
        }
        $this->pdoPool[] = $pdo;
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
    private function getConnect()
    {
        return new \PDO($this->dsn, $this->username, $this->passwd, $this->options);
    }

    /**
     * @param \PDO $pdo
     * @param \PDOException $e
     * @return \PDO
     */
    private function handleRollbackException(\PDO $pdo, \PDOException $e)
    {
        if ($this->causedByLostConnection($e)) {
            $pdo = $this->getConnect();
        }

        return $pdo;
    }

    /**
     * Determine if the given exception was caused by a lost connection.
     *
     * @param  \PDOException $e
     * @return bool
     */
    private function causedByLostConnection(\PDOException $e)
    {
        $message = $e->getMessage();
        $lostConnectionMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'Name or service not known',
            'ORA-03114',
            'Packets out of order. Expected',
        ];
        foreach ($lostConnectionMessages as $lostConnectionMessage) {
            if (stripos($message, $lostConnectionMessage) !== false) {
                return true;
            }
        }

        return false;
    }
}
