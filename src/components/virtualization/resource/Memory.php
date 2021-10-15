<?php

namespace SwFwLess\components\virtualization\resource;

class Memory
{
    const C_GROUP_TYPE = 'memory';

    public static function limit($name, $memorySize)
    {
        CGroup::setLimit(static::C_GROUP_TYPE, $name, 'limit_in_bytes', $memorySize);
    }
}
