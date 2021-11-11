<?php

namespace SwFwLess\components\database;

use SwFwLess\components\Config;

class Connector
{
    public static function connectionName($connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = Config::get('database.default');
        }
        return $connectionName;
    }

    public static function config($connectionName = null)
    {
        return Config::get('database.connections.' . static::connectionName($connectionName));
    }

    public function connect($connectionConfig)
    {
        $pdo = new \PDO(
            $connectionConfig['dsn'],
            $connectionConfig['username'],
            $connectionConfig['passwd'],
            $connectionConfig['options']
        );
        return (new PDOWrapper())->setPDO($pdo)
            ->setLastConnectedAt()
            ->setLastActivityAt()
            ->setIdleTimeout($connectionConfig['idle_timeout'] ?? 500);
    }
}
