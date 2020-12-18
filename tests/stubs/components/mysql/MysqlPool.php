<?php

class MysqlPool extends \SwFwLess\components\mysql\MysqlPool
{
    protected function getTestPDO()
    {
        require_once __DIR__ . '/../../runtime/php/pdo/TestPDO.php';

        return new TestPDO();
    }

    /**
     * @param bool $needRelease
     * @param string|null $connectionName
     * @return TestPDO
     */
    public function getConnect($needRelease = true, $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = $this->config['default'];
        }
        if (!isset($this->config['connections'][$connectionName])) {
            return null;
        }

        return $this->getTestPDO()
            ->setLastConnectedAt()
            ->setLastActivityAt()
            ->setNeedRelease($needRelease)
            ->setConnectionName($connectionName)
            ->setIdleTimeout($this->config['connections'][$connectionName]['idle_timeout'] ?? 500);
    }

    protected function poolChange($count)
    {
        //
    }
}
