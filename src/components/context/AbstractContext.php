<?php

namespace SwFwLess\components\context;

use SwFwLess\components\container\Container;

class AbstractContext
{
    /** @var self */
    protected $parentContext;

    /** @var Container */
    protected $container;

    /** @var callable */
    protected $returnCallback;

    public function setDefaultContainer()
    {
        $this->container = Container::create();
        return $this;
    }

    public function withParent(AbstractContext $parentContext)
    {
        $this->parentContext = $parentContext;
    }

    public function withReturn(callable $returnCallback)
    {
        $this->returnCallback = $returnCallback;
        return $this;
    }

    public function returnContext($data = null)
    {
        return call_user_func($this->returnCallback, $data);
    }

    public function set($id, $res)
    {
        $this->container->set($id, $res);
        return $this;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function forget($id)
    {
        $this->container->forget($id);
        return $this;
    }
}
