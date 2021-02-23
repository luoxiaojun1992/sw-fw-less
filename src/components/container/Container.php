<?php

namespace SwFwLess\components\container;

class Container extends AbstractContainer
{
    public static function createByData($data)
    {
        return static::create()->setData($data);
    }

    public static function create()
    {
        return new static();
    }
}
