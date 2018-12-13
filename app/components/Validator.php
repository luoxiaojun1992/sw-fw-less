<?php

namespace App\components;

use App\components\utils\IDCard;

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

    /**
     * @param $value
     * @param $context
     * @return false|int
     */
    public static function mobile($value, $context)
    {
        return boolval(preg_match('/^1\d{10}$/', $value));
    }

    /**
     * @param $value
     * @param $context
     * @return bool
     */
    public static function idCard($value, $context)
    {
        return IDCard::validateIDCard($value);
    }
}
