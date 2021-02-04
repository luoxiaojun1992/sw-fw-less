<?php

namespace SwFwLess\components\utils;

class ObjectUtil
{
    /**
     * @param $object
     * @return array
     */
    public static function toArray($object)
    {
        return get_object_vars($object);
    }
}
