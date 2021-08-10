<?php

namespace SwFwLess\components\container;

use SwFwLess\components\Helper;
use SwFwLess\components\utils\data\structure\Arr;

class AbstractContainer implements \ArrayAccess, \JsonSerializable
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

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->forget($offset);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
