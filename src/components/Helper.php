<?php

namespace SwFwLess\components;

use SwFwLess\bootstrap\App;
use SwFwLess\bootstrap\Kernel;
use SwFwLess\components\runtime\framework\Serializer;
use SwFwLess\components\utils\data\structure\Arr;
use SwFwLess\facades\Cache;

class Helper
{
    /**
     * @param $arr
     * @param $key
     * @return bool
     */
    public static function arrHas($arr, $key)
    {
        return Arr::arrHas($arr, $key);
    }

    /**
     * @param $arr
     * @param $key
     * @param null $default
     * @return null
     */
    public static function arrGet($arr, $key, $default = null)
    {
        return Arr::arrGet($arr, $key, $default);
    }

    /**
     * @param $arr
     * @param $key
     * @param $value
     */
    public static function arrSet(&$arr, $key, $value)
    {
        Arr::arrSet($arr, $key, $value);
    }

    /**
     * @param $arr
     * @param $key
     */
    public static function arrForget(&$arr, $key)
    {
        Arr::arrForget($arr, $key);
    }

    /**
     * @param $arr
     * @param $keys
     * @return bool
     */
    public static function nestedArrHas($arr, $keys)
    {
        return Arr::nestedArrHas($arr, $keys);
    }

    /**
     * @param $arr
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public static function nestedArrGet($arr, $keys, $default = null)
    {
        return Arr::nestedArrGet($arr, $keys, $default);
    }

    /**
     * @param $arr
     * @param $keys
     * @param $value
     */
    public static function nestedArrSet(&$arr, $keys, $value)
    {
        Arr::nestedArrSet($arr, $keys, $value);
    }

    /**
     * @param $arr
     * @param $keys
     */
    public static function nestedArrForget(&$arr, $keys)
    {
        Arr::nestedArrForget($arr, $keys);
    }

    /**
     * Determine if the given exception was caused by a lost connection.
     *
     * @param  \Throwable $e
     * @return bool
     */
    public static function causedByLostConnection(\Throwable $e)
    {
        $message = $e->getMessage();
        $lostConnectionMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'Name or service not known',
            'ORA-03114',
            'Packets out of order. Expected',
            'Connection timed out',
        ];
        foreach ($lostConnectionMessages as $lostConnectionMessage) {
            if (mb_stripos($message, $lostConnectionMessage) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param null $default
     * @return array|false|null|string
     */
    public static function env($name, $default = null)
    {
        $env = getenv($name);
        return $env !== false ? $env : $default;
    }

    /**
     * @param $name
     * @param null $default
     * @return int
     */
    public static function envInt($name, $default = null)
    {
        return intval(self::env($name, $default));
    }

    /**
     * @param $name
     * @param null $default
     * @return bool
     */
    public static function envBool($name, $default = null)
    {
        return boolval(self::env($name, $default));
    }

    /**
     * @param $name
     * @param null $default
     * @return float
     */
    public static function envDouble($name, $default = null)
    {
        return doubleval(self::env($name, $default));
    }

    /**
     * @param $name
     * @param array $default
     * @param string $separator
     * @return array|false|null|string
     */
    public static function envArray($name, $default = null, $separator = ',')
    {
        $value = self::env($name, $default);
        if (is_string($value)) {
            $value = explode($separator, $value);
        }

        return $value;
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function snake2Camel($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * @param $string
     * @return string
     */
    public static function snake2Hump($string)
    {
        return lcfirst(self::snake2Camel($string));
    }

    /**
     * @param $data
     * @param int $options
     * @param int $depth
     * @return false|string
     */
    public static function jsonEncode($data, $options = JSON_UNESCAPED_UNICODE, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }

    /**
     * @param $data
     * @param bool $assoc
     * @param int $depth
     * @param int $flags
     * @return mixed
     */
    public static function jsonDecode($data, $assoc = true, $depth = 512, $flags = 0)
    {
        return json_decode($data, $assoc, $depth, $flags);
    }

    /**
     * @param $data
     * @param false $phpSerializer
     * @return false|mixed|string
     */
    public static function serialize($data, $phpSerializer = false)
    {
        return ((!$phpSerializer) && Serializer::supportClosure()) ?
            call_user_func('\Opis\Closure\serialize', $data) :
            serialize($data);
    }

    /**
     * @return Kernel
     */
    public static function app()
    {
        return Kernel::getApp();
    }

    /**
     * @return string
     */
    public static function appVersion()
    {
        return Kernel::VERSION;
    }

    /**
     * @return string
     */
    public static function commandVersion()
    {
        return Kernel::VERSION;
    }

    /**
     * Server SAPI Name
     *
     * @return string
     */
    public static function sapi()
    {
        return Kernel::getApp()->sapi();
    }

    /**
     * @return bool
     * @deprecated
     */
    public static function runningInConsole()
    {
        $sapiName = static::sapi();
        return Arr::safeInArray($sapiName, ['cli', 'phpdbg']);
    }

    /**
     * @return bool
     */
    public static function runningInSwoole()
    {
        return static::sapi() === App::SAPI;
    }

    /**
     * @param $callback
     * @param $cacheKey
     * @param int $ttl
     * @param int $jsonOptions
     * @param int $jsonDepth
     * @return bool|string
     */
    public static function withCache(
        $callback, $cacheKey, $ttl = 0, $jsonOptions = JSON_UNESCAPED_UNICODE, $jsonDepth = 512
    )
    {
        $cache = Cache::get($cacheKey);
        if ($cache !== false) {
            return $cache;
        }

        $cache = call_user_func($callback);
        if (is_array($cache)) {
            $cache = static::jsonEncode($cache, $jsonOptions, $jsonDepth);
        }
        $cache = (string)$cache;

        Cache::set($cacheKey, $cache, $ttl);

        return $cache;
    }
}
