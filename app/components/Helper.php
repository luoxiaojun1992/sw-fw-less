<?php

namespace App\components;

class Helper
{
    public static function arrGet($arr, $key, $default = null)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}
