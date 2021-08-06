<?php

namespace SwFwLess\components\context;

use SwFwLess\components\container\Container;

class AbstractContext
{
    /** @var self */
    protected $parentContext;

    /** @var self */
    protected $childContext;

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
        $parentContext->childContext = $this;
        $this->parentContext = $parentContext;
        return $this;
    }

    public function withReturn(callable $returnCallback)
    {
        $this->returnCallback = $returnCallback;
        return $this;
    }

    public function returnContext($data = null)
    {
        if (!is_null($this->childContext)) {
            $childReturn = $this->childContext->returnContext($data);
        } else {
            $childReturn = [];
        }
        return ($childReturn !== false) ?
            array_merge($childReturn, ((array)call_user_func($this->returnCallback, $data, $childReturn))) :
            false;
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

    public function clear()
    {
        $this->container->clear();
        return $this;
    }

    public function setAll($data)
    {
        $this->container->setData($data);
        return $this;
    }

    public function parentContext()
    {
        return $this->parentContext;
    }
}
