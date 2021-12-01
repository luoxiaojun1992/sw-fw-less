<?php

namespace SwFwLess\components\di;

use DI\ContainerBuilder;
use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\traits\Singleton;
use Zipkin\Span;

class Container
{
    const DEFAULT_DI_SWITCH = true;

    use Singleton;

    /** @var \DI\Container  */
    private $diContainer;

    public static function diSwitch()
    {
        return \SwFwLess\components\Config::get('di_switch', static::DEFAULT_DI_SWITCH);
    }

    public static function routeDiSwitch()
    {
        return static::diSwitch() && \SwFwLess\components\Config::get('route_di_switch');
    }

    /**
     * Container constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->diContainer = (new ContainerBuilder())->build();
    }

    public function __call($name, $arguments)
    {
        $callback = function () use ($name, $arguments) {
            return call_user_func_array([$this->diContainer, $name], $arguments);
        };

        if (in_array($name, ['get', 'make', 'call'])) {
            return Scheduler::withoutPreemptive($callback);
        }

        return call_user_func($callback);
    }

    /**
     * @param $callable
     * @param array $parameters
     * @param null $swfRequest
     * @return mixed
     * @throws \Throwable
     */
    public function callWithTrace($callable, $parameters = [], $swfRequest = null)
    {
        $swfRequest = $swfRequest ?? \SwFwLess\components\functions\request();
        $spanName = $this->callableToSpanName($callable);

        return $swfRequest->getTracer()->clientSpan($spanName, function (Span $span) use ($callable, $parameters) {
            //todo add metrics
            return $this->call($callable, $parameters);
        });
    }

    /**
     * @param $callable
     * @return mixed|string
     */
    protected function callableToSpanName($callable)
    {
        $spanName = 'callable';
        if (is_array($callable)) {
            $objectOrClass = $callable[0];
            if (is_object($objectOrClass)) {
                $spanName = get_class($objectOrClass);
            } else {
                $spanName = $objectOrClass;
            }
        } elseif (is_string($callable)) {
            $spanName = $callable;
        } elseif (is_object($callable)) {
            $spanName = get_class($callable);
        }

        return str_replace('\\', '/', $spanName);
    }
}
