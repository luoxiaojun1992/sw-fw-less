<?php

namespace SwFwLess\components\swoole\db;

use Swoole\Table;

class MemoryDB
{
    /** @var Table[] */
    public static $swTables = [];

    public static function init($memoryDBConfig = [])
    {
        foreach ($memoryDBConfig['tables'] ?? [] as $tableConfig) {
            $table = new Table($tableConfig['size']);
            foreach ($tableConfig['columns'] ?? [] as $columnConfig) {
                $table->column($columnConfig['name'], $columnConfig['type'], $columnConfig['size']);
            }
            $table->create();
            static::$swTables[$tableConfig['name']] = $table;
        }
    }

    public static function reload()
    {

    }
}
