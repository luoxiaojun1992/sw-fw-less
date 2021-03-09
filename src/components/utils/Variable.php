<?php

namespace SwFwLess\components\utils;

class Variable
{
    /**
     * @param $variable
     * @param $value
     * @throws \Exception
     */
    public static function assignValue(&$variable, $value)
    {
        $varType = gettype($variable);
        $valType = gettype($value);
        if ($varType !== $valType) {
            throw new \Exception('Type error, var type:' . $varType . ', val type:' . $valType);
        }

        if (is_object($value)) {
            $varClass = get_class($variable);
            if (!($value instanceof $varClass)) {
                throw new \Exception(
                    'Object Type error, var type:' . $varClass . ', val type:' . get_class($value)
                );
            }
        }

        $variable = $value;
    }

    public static function oneNull(...$vars)
    {
        $result = false;

        foreach ($vars as $var) {
            if (is_null($var)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public static function allNull(...$vars)
    {
        $result = false;

        foreach ($vars as $var) {
            if (is_null($var)) {
                $result = true;
            } else {
                $result = false;
                break;
            }
        }

        return $result;
    }

    public static function oneNotNull(...$vars)
    {
        return (!(static::allNull(...$vars)));
    }

    public static function allNotNull(...$vars)
    {
        return (!(static::oneNull(...$vars)));
    }
}
