<?php

namespace SwFwLess\components\context;

class Context extends AbstractContext
{
    public static function create()
    {
        return (new static())->setDefaultContainer();
    }
}
