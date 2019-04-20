<?php

namespace SwFwLess\components\di;

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
        return call_user_func_array([$this->diContainer, $name], $arguments);
    }
}
