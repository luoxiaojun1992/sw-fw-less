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

    public static function subCGroupDir($type, $name)
    {
        return static::cGroupDir() . '/' . $type . '/' . $name;
    }

    public static function subCGroupExists($type, $name)
    {
        return is_dir(static::subCGroupDir($type, $name));
    }

    public static function delCGroup($type, $name)
    {
        $subCGroupDir = static::subCGroupDir($type, $name);
        if (shell_exec('rm -rf ' . $subCGroupDir) === false) {
            return false;
        }
        return rmdir($subCGroupDir);
    }

    public static function createSubCGroup($type, $name, $pid = null)
    {
        $subCGroupDir = static::subCGroupDir($type, $name);
        if (!is_dir($subCGroupDir)) {
            if (!mkdir($subCGroupDir, 0755, true)) {
                return false;
            }
            if (!chmod($subCGroupDir, 0755)) {
                return false;
            }
            if (!is_null($pid)) {
                if (file_put_contents($subCGroupDir . '/cgroup.procs', $pid, LOCK_EX) === false) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public static function addCGroupProcess($type, $name, $pid)
    {
        $subCGroupDir = static::subCGroupDir($type, $name);
        return file_put_contents(
            $subCGroupDir . '/cgroup.procs',
                PHP_EOL . $pid,
                FILE_APPEND|LOCK_EX
            ) !== false;
    }

    public static function setLimit($type, $name, $index, $value)
    {
        $subCGroupDir = static::subCGroupDir($type, $name);
        return file_put_contents($subCGroupDir . '/' . $type . '.' . $index, $value) !== false;
    }
}
