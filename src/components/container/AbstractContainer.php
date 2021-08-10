<?php

namespace SwFwLess\components\container;

use SwFwLess\components\Helper;
use SwFwLess\components\utils\data\structure\Arr;

class AbstractContainer
{
    protected $data = [];

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __unset($name)
    {
        $this->forget($name);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function set($id, $res)
    {
        $setter = 'set' . Helper::snake2Camel($id);
        if (method_exists($this, $setter)) {
            call_user_func_array([$this, $setter], [$res]);
        } else {
            Arr::nestedArrSet($this->data, $id, $res);
        }
        return $this;
    }

    public function get($id)
    {
        $getter = 'get' . Helper::snake2Camel($id);
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        }
        return Arr::nestedArrGet($this->data, $id, null);
    }

    public function has($id)
    {
        $getter = 'has' . Helper::snake2Camel($id);
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        }
        return Arr::nestedArrHas($this->data, $id);
    }

    public function forget($id)
    {
        $setter = 'remove' . Helper::snake2Camel($id);
        if (method_exists($this, $setter)) {
            call_user_func([$this, $setter]);
        } else {
            Arr::nestedArrForget($this->data, $id);
        }
        return $this;
    }

    public function setData($data)
    {
        foreach ($data as $key => $datum) {
            $this->set($key, $datum);
        }
        return $this;
    }

    public function getData()
    {
        $data = [];
        foreach ($this->data as $key => $datum) {
            $data[$key] = $this->get($key);
        }
        return $data;
    }

    public function clear()
    {
        foreach ($this->data as $key => $datum) {
            $this->forget($key);
        }
        return $this;
    }
}
