<?php

namespace SwFwLess\components\utils;

class CallableUtil
{
    /**
     * @param $callable
     * @return string
     */
    public static function getName($callable)
    {
        $name = 'callable';
        if (is_array($callable)) {
            $objectOrClass = $callable[0];
            if (is_object($objectOrClass)) {
                $name = get_class($objectOrClass);
            } elseif (is_string($objectOrClass)) {
                $name = $objectOrClass;
            }
        } elseif (is_string($callable)) {
            $name = $callable;
        } elseif (is_object($callable)) {
            $name = get_class($callable);
        }

        return $name;
    }
}
