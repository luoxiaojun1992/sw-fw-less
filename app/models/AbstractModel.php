<?php

namespace App\models;

use App\components\Helper;

abstract class AbstractModel implements \JsonSerializable, \ArrayAccess
{
    private $attributes = [];

    /**
     * @param $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    public function setAttribute($name, $value)
    {
        $setter = 'set' . Helper::snake2Camel($name);
        if (method_exists($this, $setter)) {
            call_user_func_array([$this, $setter], [$value]);
        } else {
            $this->attributes[$name] = $value;
        }
    }

    public function getAttribute($name)
    {
        $getter = 'get' . Helper::snake2Camel($name);
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        } else {
            return $this->attributes[$name];
        }
    }

    public function removeAttribute($name)
    {
        $setter = 'remove' . Helper::snake2Camel($name);
        if (method_exists($this, $setter)) {
            call_user_func([$this, $setter]);
        } else {
            unset($this->attributes[$name]);
        }
    }

    public function attributeExists($name)
    {
        $getter = 'exist' . Helper::snake2Camel($name);
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        } else {
            return array_key_exists($name, $this->toArray());
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
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
        return $this->attributeExists($offset);
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
        return $this->getAttribute($offset);
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
        $this->setAttribute($offset, $value);
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
        $this->removeAttribute($offset);
    }
}
