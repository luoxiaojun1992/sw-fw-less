<?php

namespace SwFwLess\components\utils\data\structure;

class ObjectUtil
{
    /**
     * @param $object
     * @return array
     */
    public static function toArray($object)
    {
        if (is_object($object)) {
            return array_map([static::class, __METHOD__], get_object_vars($object));
        } else {
            return $object;
        }
    }
}
