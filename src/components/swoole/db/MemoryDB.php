<?php

namespace SwFwLess\components\swoole\db;

use SwFwLess\components\swoole\Scheduler;
use Swoole\Table;

class MemoryDB
{
    protected static $instance;

    protected $config = [];

    /** @var Table[] */
    protected $swTables = [];

    public static function create($memoryDBConfig = [])
    {
        if (static::$instance instanceof static) {
            return static::$instance;
        }

        return (static::$instance = new static($memoryDBConfig));
    }

    public function __construct($memoryDBConfig = [])
    {
        $this->config = $memoryDBConfig;
        $this->init();
    }

    public function init()
    {
        foreach ($this->config['tables'] ?? [] as $tableConfig) {
            $table = new Table($tableConfig['size']);
            foreach ($tableConfig['columns'] ?? [] as $columnConfig) {
                $table->column($columnConfig['name'], $columnConfig['type'], $columnConfig['size']);
            }
            $table->create();
            $this->swTables[$tableConfig['name']] = $table;
        }
    }

    public function putTable($name, $table)
    {
        Scheduler::withoutPreemptive(function () use ($name, $table) {
            $this->swTables[$name] = $table;
        });
    }

    public function reload()
    {
        $this->swTables = [];
        $this->init();;
    }
}
