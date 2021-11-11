<?php

namespace SwFwLess\components\database\traits;

use SwFwLess\components\Helper;

trait PDOTransaction
{
    private $transactionLevel = 0;

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->transactionLevel > 0;
    }

    public function begin()
    {
        if ($this->transactionLevel <= 0) {
            $this->beginTransaction();
            $this->transactionLevel++;
        } else {
            try {
                $this->pdo->exec('SAVEPOINT trans.' . (string)($this->transactionLevel + 1));
                $this->transactionLevel++;
            } catch (\PDOException $e) {
                $this->handleBeginException($e);
            }
        }
    }

    /**
     * @param $e
     */
    public function handleBeginException($e)
    {
        if (Helper::causedByLostConnection($e)) {
            $this->transactionLevel = 0;
            $this->reconnect();
        }

        throw $e;
    }

    public function rollback()
    {
        try {
            if ($this->transactionLevel > 1) {
                $this->pdo->exec('ROLLBACK TO SAVEPOINT trans.' . (string)$this->transactionLevel);
                $this->transactionLevel--;
            } elseif ($this->transactionLevel == 1) {
                $this->pdo->rollBack();
                $this->transactionLevel--;
            }
        } catch (\PDOException $e) {
            $this->handleRollbackException($e);
        }
    }

    /**
     * @param \PDOException $e
     */
    public function handleRollbackException(\PDOException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $this->transactionLevel = 0;
            $this->reconnect();
        }

        throw $e;
    }

    public function commit()
    {
        try {
            if ($this->transactionLevel > 1) {
                $this->pdo->exec('RELEASE SAVEPOINT trans.' . (string)$this->transactionLevel);
                $this->transactionLevel--;
            } elseif ($this->transactionLevel == 1) {
                $this->pdo->commit();
                $this->transactionLevel--;
            }
        } catch (\PDOException $e) {
            $this->handleCommitException($e);
        }
    }

    /**
     * @param \PDOException $e
     */
    public function handleCommitException(\PDOException $e)
    {
        if (Helper::causedByLostConnection($e)) {
            $this->transactionLevel = 0;
            $this->reconnect();
        }

        throw $e;
    }
}
