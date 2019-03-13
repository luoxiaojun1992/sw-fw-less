<?php

if (!function_exists('config')) {
    function config($key, $default = null) {
        return \App\components\Config::get($key, $default);
    }
}

if (!function_exists('callHelper')) {
    function helper($method, ...$args) {
        return call_user_func_array([\App\components\Helper::class, $method], $args);
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
     * @return \App\components\http\Request
     */
    function request()
    {
        return \App\components\http\Request::fetch();
    }
}
