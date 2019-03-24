<?php

namespace App\components\di;

use App\facades\File;
use DI\ContainerBuilder;

class Container
{
    private static $instance;

    /** @var \DI\Container  */
    private $diContainer;

    /**
     * @return Container
     * @throws \Exception
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self();
    }

    /**
     * Container constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->enableCompilation(File::path('runtime/compiled'));
        $this->diContainer = $containerBuilder->build();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->diContainer, $name], $arguments);
    }
}
