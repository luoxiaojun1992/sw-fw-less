<?php

namespace SwFwLess\components;

use SwFwLess\bootstrap\App;

class Helper
{
    /**
     * @param $arr
     * @param $key
     * @param null $default
     * @return null
     */
    public static function arrGet($arr, $key, $default = null)
    {
        if (is_null($arr)) {
            return $default;
        }

        return array_key_exists($key, $arr) ? $arr[$key] : $default;
    }

    /**
     * @param $arr
     * @param $key
     * @param $value
     */
    public static function arrSet(&$arr, $key, $value)
    {
        $arr[$key] = $value;
    }

    /**
     * @param $arr
     * @param $key
     */
    public static function arrForget(&$arr, $key)
    {
        unset($arr[$key]);
    }

    /**
     * @param $arr
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public static function nestedArrGet($arr, $keys, $default = null)
    {
        if (is_string($keys)) {
            $keys = explode('.', $keys);
        } else {
            if (!is_array($keys)) {
                return $default;
            }
        }

        foreach ($keys as $key) {
            if (is_array($arr) && array_key_exists($key, $arr)) {
                $arr = $arr[$key];
            } else {
                $arr = $default;
                break;
            }
        }

        return $arr;
    }

    /**
     * @param $arr
     * @param $keys
     * @param $value
     */
    public static function nestedArrSet(&$arr, $keys, $value)
    {
        if (is_null($keys)) {
            $arr = $value;
            return;
        }

        $keys = explode('.', $keys);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($arr[$key]) || !is_array($arr[$key])) {
                $arr[$key] = [];
            }

            $arr = &$arr[$key];
        }

        $arr[array_shift($keys)] = $value;
    }

    /**
     * @param $arr
     * @param $keys
     */
    public static function nestedArrForget(&$arr, $keys)
    {
        $keys = explode('.', $keys);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (isset($arr[$key]) && is_array($arr[$key])) {
                $arr = &$arr[$key];
            }
        }

        unset($arr[array_shift($keys)]);
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
