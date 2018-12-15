<?php

namespace App\components;

class LogStreamWrapper
{
    private $host;

    public static function register()
    {
        stream_wrapper_register('log', __CLASS__);
    }

    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->host = $url['host'];

        return true;
    }

    function stream_write($data)
    {
        $method = $this->host;
        \App\facades\Log::$method($data);
        $dataLen = strlen($data);
        return $dataLen;
    }
}
