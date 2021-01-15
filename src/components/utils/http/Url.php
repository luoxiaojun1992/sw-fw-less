<?php

namespace SwFwLess\components\utils\http;

class Url
{
    protected static $decodedCache = [];

    protected static $decodedCacheCount = 0;

    public static function decode($url)
    {
        //TODO
        return rawurldecode($url);
    }
}
