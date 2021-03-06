<?php

namespace SwFwLess\components\utils\language;

class Chinese
{
    public static function containsChinese($str)
    {
        return boolval(preg_match('/([\x{4E00}-\x{9FA5}]|[\x{9FA6}-\x{9FCB}]|[\x{3400}-\x{4DB5}]|[\x{20000}-\x{2A6D6}]|[\x{2A700}-\x{2B734}]|[\x{2B740}-\x{2B81D}]|[\x{2F00}-\x{2FD5}]|[\x{2E80}-\x{2EF3}]|[\x{F900}-\x{FAD9}]|[\x{2F800}-\x{2FA1D}]|[\x{E815}-\x{E86F}]|[\x{E400}-\x{E5E8}]|[\x{E600}-\x{E6CF}]|[\x{31C0}-\x{31E3}]|[\x{2FF0}-\x{2FFB}]|[\x{3105}-\x{3120}]|\x{3128}|[\x{31A0}-\x{31BA}]|\x{3007})/u', $str));
    }
}
