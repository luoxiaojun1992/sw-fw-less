<?php

namespace SwFwLess\components\context;

use SwFwLess\components\container\Container;

class AbstractContext
{
    /** @var self[] */
    protected $contextChildren = [];

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
        $parentContext->contextChildren[] = $this;
        return $this;
    }

    public function withReturn(callable $returnCallback)
    {
        $this->returnCallback = $returnCallback;
        return $this;
    }

    public function returnContext($data = null)
    {
        $returnData = [];
        foreach ($this->contextChildren as $contextChild) {
            if (($childReturnData = $contextChild->returnContext($data)) === false) {
                return false;
            }
            $returnData = array_merge($returnData, $childReturnData);
        }
        $currentReturnData = call_user_func($this->returnCallback, $data, $returnData);
        return ($currentReturnData === false) ? false : array_merge($returnData, (array)$currentReturnData);
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

    public function childContext($id = 0)
    {
        return $this->contextChildren[$id] ?? null;
    }

    public function contextChildren()
    {
        return $this->contextChildren;
    }
}
