<?php

namespace App\components\swoole\counter;

use App\facades\Container;
use Swoole\Table;

class Counter
{
    /** @var Table */
    private static $swTable;

    public static function init()
    {
        if (!(self::$swTable instanceof Table)) {
            self::$swTable = new Table(1024);
            self::$swTable->column('worker_id', Table::TYPE_INT, 8);
            self::$swTable->column('count', Table::TYPE_INT, 8);
            self::$swTable->create();
        }
    }

    public static function reload()
    {
        foreach (self::$swTable as $key => $row) {
            $workerId = Container::get('swoole.server')->worker_id;
            if ($row['worker_id'] == $workerId) {
                self::$swTable->set($key, ['count' => 0, 'worker_id' => $workerId]);
            }
        }
    }

    public static function incr($key, $incrBy = 1)
    {
        $workerId = Container::get('swoole.server')->worker_id;
        self::$swTable->incr($key . ':' . $workerId, 'count', $incrBy);
        self::$swTable->set($key . ':' . $workerId, ['worker_id' => $workerId]);
    }

    public static function decr($key, $decrBy = 1)
    {
        $workerId = Container::get('swoole.server')->worker_id;
        self::$swTable->decr($key . ':' . $workerId, 'count', $decrBy);
        self::$swTable->set($key . ':' . $workerId, ['worker_id' => $workerId]);
    }

    public static function get($key)
    {
        $count = 0;

        foreach (self::$swTable as $id => $row) {
            if ($id === $key . ':' . $row['worker_id']) {
                $count += $row['count'];
            }
        }

        return $count;
    }
}
