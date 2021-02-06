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
}
