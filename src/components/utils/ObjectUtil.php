<?php

namespace SwFwLess\components\utils;

/**
 * @deprecated
 */
class ObjectUtil
{
    /**
     * @param $object
     * @return array
     */
    public static function toArray($object)
    {
        return \SwFwLess\components\utils\data\structure\ObjectUtil::toArray($object);
    }
}
