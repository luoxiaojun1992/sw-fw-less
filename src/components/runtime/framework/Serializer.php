<?php

namespace SwFwLess\components\runtime\framework;

class Serializer
{
    public static function supportClosure()
    {
        return function_exists('\Opis\Closure\serialize');
    }
}
