<?php

namespace SwFwLessTest\stubs\components\database;

class ConnectionPool extends \SwFwLess\components\database\ConnectionPool
{
    protected function getTestPDOWrapper()
    {
        require_once __DIR__ . '/PDOWrapper.php';

        return new PDOWrapper();
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

        return $this->getTestPDOWrapper()
            ->setLastConnectedAt()
            ->setLastActivityAt()
            ->setNeedRelease($needRelease)
            ->setConnectionName($connectionName)
            ->setIdleTimeout($this->config['connections'][$connectionName]['idle_timeout'] ?? 500)
            ->setConnectionPool($this);
    }

    protected function poolChange($count)
    {
        //
    }
}
