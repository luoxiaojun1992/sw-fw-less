<?php

namespace SwFwLess\components\utils;

class Arr
{
    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function safeInArray($needle, $haystack)
    {
        return in_array($needle, $haystack, true);
    }
}
