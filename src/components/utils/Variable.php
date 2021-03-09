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
            throw new \Exception('Type error');
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
