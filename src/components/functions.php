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
    return helper('env', $name, $default);
}

function envInt($name, $default = null)
{
    return helper('envInt', $name, $default);
}

function envDouble($name, $default = null)
{
    return helper('envDouble', $name, $default);
}

function envArray($name, $default = null, $separator = ',')
{
    return helper('envArray', $name, $default, $separator);
}

function envBool($name, $default = null)
{
    return helper('envBool', $name, $default);
}

function appVersion()
{
    return helper('apiVersion');
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
    return helper('sapi');
}
