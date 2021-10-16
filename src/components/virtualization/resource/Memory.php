<?php

namespace SwFwLess\components\virtualization\resource;

class Memory
{
    const C_GROUP_TYPE = 'memory';

    public static function limit($name, $memorySize)
    {
        return CGroup::setLimit(static::C_GROUP_TYPE, $name, 'limit_in_bytes', $memorySize);
    }

    public static function subCGroupExists($name)
    {
        return CGroup::subCGroupExists(static::C_GROUP_TYPE, $name);
    }

    public static function createCGroup($name, $pid = null)
    {
        return CGroup::createSubCGroup(static::C_GROUP_TYPE, $name, $pid);
    }

    public static function addCGroupProcess($name, $pid)
    {
        return CGroup::addCGroupProcess(static::C_GROUP_TYPE, $name, $pid);
    }

    public static function delCGroup($name)
    {
        return CGroup::delCGroup(static::C_GROUP_TYPE, $name);
    }
}
