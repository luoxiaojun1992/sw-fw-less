<?php

//You need to check the duplication of these functions.
//Don't add an new function!!!

if (!function_exists('config')) {
    /**
     * @param $key
     * @param null $default
     * @return mixed
     * @deprecated
     */
    function config($key, $default = null) {
        return \SwFwLess\components\Config::get($key, $default);
    }
}

if (!function_exists('helper')) {
    /**
     * @param $method
     * @param mixed ...$args
     * @return mixed
     * @deprecated
     */
    function helper($method, ...$args) {
        return call_user_func_array([\SwFwLess\components\Helper::class, $method], $args);
    }
}

if (!function_exists('env')) {
    /**
     * @param $name
     * @param null $default
     * @return mixed
     * @deprecated
     */
    function env($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('envInt')) {
    /**
     * @param $name
     * @param null $default
     * @return mixed
     * @deprecated
     */
    function envInt($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('envDouble')) {
    /**
     * @param $name
     * @param null $default
     * @return mixed
     * @deprecated
     */
    function envDouble($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('envArray')) {
    /**
     * @param $name
     * @param null $default
     * @param string $separator
     * @return mixed
     * @deprecated
     */
    function envArray($name, $default = null, $separator = ',')
    {
        return helper(__METHOD__, $name, $default, $separator);
    }
}

if (!function_exists('envBool')) {
    /**
     * @param $name
     * @param null $default
     * @return mixed
     * @deprecated
     */
    function envBool($name, $default = null)
    {
        return helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('appVersion')) {
    /**
     * @return mixed
     * @deprecated
     */
    function appVersion()
    {
        return helper(__METHOD__);
    }
}

if (!function_exists('request')) {
    /**
     * @return \SwFwLess\components\http\Request
     * @deprecated
     */
    function request()
    {
        return \SwFwLess\components\http\Request::fetch();
    }
}

if (!function_exists('event')) {
    /**
     * @param $event
     * @return \Cake\Event\Event
     * @deprecated
     */
    function event($event)
    {
        return \SwFwLess\facades\Event::dispatch($event);
    }
}

if (!function_exists('sapi')) {
    /**
     * @return mixed
     * @deprecated
     */
    function sapi()
    {
        return helper(__METHOD__);
    }
}
