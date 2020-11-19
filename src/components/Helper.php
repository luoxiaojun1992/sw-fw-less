<?php

namespace SwFwLess\components;

use SwFwLess\bootstrap\App;
use SwFwLess\components\utils\Arr;

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
        ];
        foreach ($lostConnectionMessages as $lostConnectionMessage) {
            if (stripos($message, $lostConnectionMessage) !== false) {
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
     * @return false|string
     */
    public static function jsonEncode($data, $options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($data, $options);
    }

    /**
     * @param $data
     * @param bool $assoc
     * @return mixed
     */
    public static function jsonDecode($data, $assoc = true)
    {
        return json_decode($data, $assoc);
    }

    /**
     * @return string
     */
    public static function appVersion()
    {
        return App::VERSION;
    }

    /**
     * @return string
     */
    public static function sapi()
    {
        return App::SAPI === 'swoole' ? App::SAPI : php_sapi_name();
    }

    /**
     * @return bool
     */
    public static function runningInConsole()
    {
        $sapiName = static::sapi();
        return $sapiName === 'cli' || $sapiName === 'phpdbg';
    }

    /**
     * @return bool
     */
    public static function runningInSwoole()
    {
        return static::sapi() === 'swoole';
    }
}
