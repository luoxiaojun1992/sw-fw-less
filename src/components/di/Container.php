<?php

namespace SwFwLess\components\di;

use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\traits\Singleton;
use DI\ContainerBuilder;

class Container
{
    use Singleton;

    /** @var \DI\Container  */
    private $diContainer;

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

        if (in_array($name, ['get', 'make'])) {
            return Scheduler::withoutPreemptive($callback);
        }

        return call_user_func($callback);
    }
}
