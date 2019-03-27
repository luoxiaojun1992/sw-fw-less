<?php

namespace App\components\log;

use App\facades\Log;

/**
 * Class LogStreamWrapper
 * @package App\components\log
 */
class LogStreamWrapper
{
    private $host;

    public static function register()
    {
        stream_wrapper_register('log', __CLASS__);
    }

    /**
     * @param $path
     * @param $mode
     * @param $options
     * @param $opened_path
     * @return bool
     */
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->host = $url['host'];

        return true;
    }

    /**
     * @param $data
     * @return int
     */
    function stream_write($data)
    {
        $method = $this->host;
        Log::$method($data);
        return strlen($data);
    }
}
