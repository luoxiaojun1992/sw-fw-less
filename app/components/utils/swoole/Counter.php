<?php

namespace App\components\utils\swoole;

use Swoole\Table;

class Counter
{
    /** @var Table */
    private static $swTable;

    public static function init()
    {
        if (!(self::$swTable instanceof swTable)) {
            self::$swTable = new Table(1024);
            self::$swTable->column('count', Table::TYPE_INT, 8);
            self::$swTable->create();
        }
    }

    public static function incr($key, $incrby = 1)
    {
        self::$swTable->incr($key, 'count', $incrby);
    }

    public static function decr($key, $decrby = 1)
    {
        self::$swTable->decr($key, 'count', $decrby);
    }

    public static function get($key)
    {
        return self::$swTable->get($key, 'count');
    }
}
