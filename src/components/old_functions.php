<?php

//You need to check the duplication of these functions.
//Don't add any new function!!!

if (!function_exists('config')) {
    /**
     * @param $key
     * @param null $default
     * @return mixed
     * @deprecated
     */
    function config($key, $default = null) {
        return \SwFwLess\components\functions\config($key, $default);
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
        return \SwFwLess\components\functions\helper($method, ...$args);
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
        return \SwFwLess\components\functions\helper(__METHOD__, $name, $default);
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
        return \SwFwLess\components\functions\helper(__METHOD__, $name, $default);
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
        return \SwFwLess\components\functions\helper(__METHOD__, $name, $default);
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
        return \SwFwLess\components\functions\helper(__METHOD__, $name, $default, $separator);
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
        return \SwFwLess\components\functions\helper(__METHOD__, $name, $default);
    }
}

if (!function_exists('appVersion')) {
    /**
     * @return mixed
     * @deprecated
     */
    function appVersion()
    {
        return \SwFwLess\components\functions\helper(__METHOD__);
    }
}

if (!function_exists('request')) {
    /**
     * @return \SwFwLess\components\http\Request
     * @deprecated
     */
    function request()
    {
        return \SwFwLess\components\functions\request();
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
        return \SwFwLess\components\functions\event($event);
    }
}

if (!function_exists('sapi')) {
    /**
     * @return mixed
     * @deprecated
     */
    function sapi()
    {
        return \SwFwLess\components\functions\helper(__METHOD__);
    }
}
