<?php

namespace SwFwLess\components\utils\html;

class Purifier
{
    public static function purify($html)
    {
        return (new \HTMLPurifier(
            \HTMLPurifier_Config::createDefault()
        ))->purify($html);
    }
}
