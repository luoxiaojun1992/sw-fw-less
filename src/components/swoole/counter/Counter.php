<?php

namespace SwFwLess\components\swoole\counter;

use SwfwLess\components\swoole\Server;
use Swoole\Table;

class Counter
{
    /** @var Table */
    private static $swTable;

    public static function init($tableConfig = [])
    {
        if (!(self::$swTable instanceof Table)) {
            self::$swTable = new Table($tableConfig['size'] ?? 1024);
            self::$swTable->column('worker_id', Table::TYPE_INT, 8);
            self::$swTable->column('count', Table::TYPE_INT, 8);
            self::$swTable->create();
        }
    }

    public static function reload()
    {
        $workerId = Server::getInstance()->worker_id;
        foreach (self::$swTable as $key => $row) {
            if ($row['worker_id'] == $workerId) {
                self::$swTable->set($key, ['count' => 0, 'worker_id' => $workerId]);
            }
        }
    }

    public static function incr($key, $incrBy = 1)
    {
        $workerId = Server::getInstance()->worker_id;
        self::$swTable->incr($key . ':' . $workerId, 'count', $incrBy);
        self::$swTable->set($key . ':' . $workerId, ['worker_id' => $workerId]);
    }

    public static function decr($key, $decrBy = 1)
    {
        $workerId = Server::getInstance()->worker_id;
        self::$swTable->decr($key . ':' . $workerId, 'count', $decrBy);
        self::$swTable->set($key . ':' . $workerId, ['worker_id' => $workerId]);
    }

    public static function get($key)
    {
        $count = 0;

        foreach (self::$swTable as $id => $row) {
            if ($id === ($key . ':' . $row['worker_id'])) {
                $count += $row['count'];
            }
        }

        return $count;
    }
}
