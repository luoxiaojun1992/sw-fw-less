<?php

namespace App\facades;

abstract class AbstractFacade
{
    protected static function getAccessor()
    {
        return null;
    }

    public static function __callStatic($name, $arguments)
    {
        $accessor = static::getAccessor();
        if ($accessor) {
            return call_user_func_array([$accessor, $name], $arguments);
        }

        return null;
    }
}
