<?php

namespace App\facades;

/**
 * Class Container
 *
 * @method static mixed get($name)
 * @method static mixed make($name, array $parameters = [])
 * @method static bool has($name)
 * @method static object injectOn($instance)
 * @method static mixed call($callable, array $parameters = [])
 * @method static set(string $name, $value)
 * @package App\facades
 */
class Container extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\di\Container::create();
    }
}
