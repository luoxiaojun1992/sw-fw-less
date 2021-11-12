<?php

namespace SwFwLess\components\database;

use SwFwLess\components\Config;

class Database
{
    public static function config()
    {
        return Config::get('database');
    }

    public static function poolChangeEvent()
    {
        return Config::get('database.pool_change_event');
    }

    public static function reportPoolChange()
    {
        return Config::get('database.report_pool_change');
    }

    public static function initConnectionPool()
    {
        ConnectionPool::create(static::config());
    }

    public static function init()
    {
        static::initConnectionPool();
    }
}
