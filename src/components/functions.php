<?php

namespace SwFwLess\components\functions;

function config($key, $default = null)
{
    return \SwFwLess\components\Config::get($key, $default);
}

function helper($method, ...$args)
{
    return call_user_func_array([\SwFwLess\components\Helper::class, $method], $args);
}

function env($name, $default = null)
{
    return helper(__METHOD__, $name, $default);
}

function envInt($name, $default = null)
{
    return helper(__METHOD__, $name, $default);
}

function envDouble($name, $default = null)
{
    return helper(__METHOD__, $name, $default);
}

function envArray($name, $default = null, $separator = ',')
{
    return helper(__METHOD__, $name, $default, $separator);
}

function envBool($name, $default = null)
{
    return helper(__METHOD__, $name, $default);
}

function appVersion()
{
    return helper(__METHOD__);
}

/**
 * @return \SwFwLess\components\http\Request
 */
function request()
{
    return \SwFwLess\components\http\Request::fetch();
}

function event($event)
{
    return \SwFwLess\facades\Event::dispatch($event);
}

function sapi()
{
    return helper(__METHOD__);
}
