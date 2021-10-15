<?php

namespace SwFwLess\components\virtualization\resource;

class CGroup
{
    public static function cGroupDir()
    {
        return '/sys/fs/cgroup';
    }

    public static function support()
    {
        return is_dir(static::cGroupDir());
    }

    public static function createSubCGroup($type, $name, $pid)
    {
        $subCGroupDir = static::cGroupDir() . '/' . $type . '/' . $name;
        if (!is_dir($subCGroupDir)) {
            if (!mkdir($subCGroupDir, 0755, true)) {
                return false;
            }
            if (!chmod($subCGroupDir, 0755)) {
                return false;
            }
            if (file_put_contents($subCGroupDir . '/cgroup.procs', $pid) === false) {
                return false;
            }
            return true;
        }
        return false;
    }

    public static function setLimit($type, $name, $index, $value)
    {
        $subCGroupDir = static::cGroupDir() . '/' . $type . '/' . $name;
        file_put_contents($subCGroupDir . '/' . $type . '.' . $index, $value);
    }
}
