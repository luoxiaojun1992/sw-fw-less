<?php

namespace SwFwLess\components\chaos;

use Swoole\Table;

class FaultStore
{
    /** @var Table */
    private static $swTable;

    public static function init()
    {
        $chaosSwitch = \SwFwLess\components\functions\config('chaos.switch', false);
        if ($chaosSwitch) {
            if (!(self::$swTable instanceof Table)) {
                self::$swTable = new Table(1024);
                self::$swTable->column('fault', Table::TYPE_STRING, 255);
                self::$swTable->create();
            }
        }
    }

    public static function get($key)
    {
        return self::$swTable->get($key, 'fault');
    }

    public static function set($key, $fault)
    {
        return self::$swTable->set($key, ['fault' => $fault]);
    }
}
