<?php

namespace SwFwLess\components\utils\language;

class Chinese
{
    public static function containsChinese($str)
    {
        //TODO
        return boolval(preg_match('/([\x{4E00}-\x{9FA5}]|[\x{3400}-\x{4DB5}])/u', $str));
    }
}
