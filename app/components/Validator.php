<?php

namespace App\components;

class Validator
{
    /**
     * @param $value
     * @param $context
     * @return bool
     */
    public static function string($value, $context)
    {
        return is_string($value);
    }
}
