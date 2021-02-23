<?php

namespace SwFwLess\components\container;

class Container extends AbstractContainer
{
    public static function createByData($data)
    {
        $container = new static;
        $container->data = $data;

        return $container;
    }

    public static function create()
    {
        return new static();
    }
}
