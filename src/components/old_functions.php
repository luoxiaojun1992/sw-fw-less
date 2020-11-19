<?php

//You need to check the duplication of these functions.
//Don't add an new function!!!

if (!function_exists('config')) {
    function config($key, $default = null) {
        return \SwFwLess\components\Config::get($key, $default);
    }
}

if (!function_exists('helper')) {
    function helper($method, ...$args) {
        return call_user_func_array([\SwFwLess\components\Helper::class, $method], $args);
    }
}

if (!function_exists('env')) {
    function env($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('envInt')) {
    function envInt($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('envDouble')) {
    function envDouble($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('envArray')) {
    function envArray($name, $default = null, $separator = ',')
    {
        return helper(__METHOD__, $name, $default, $separator);
    }
}

if (!function_exists('envBool')) {
    function envBool($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('appVersion')) {
    function appVersion()
    {
        return helper(__METHOD__);
    }
}

if (!function_exists('request')) {
    /**
     * @return \SwFwLess\components\http\Request
     */
    function request()
    {
        return \SwFwLess\components\http\Request::fetch();
    }
}

if (!function_exists('event')) {
    function event($event)
    {
        return \SwFwLess\facades\Event::dispatch($event);
    }
}

if (!function_exists('sapi')) {
    function sapi()
    {
        return helper(__METHOD__);
    }
}
