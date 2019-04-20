<?php

namespace SwFwLess\models\traits;

use SwFwLess\components\Helper;

trait ModelAttributes
{
    protected $originalAttributes = [];
    protected $attributes = [];
    protected $fillable = ['*'];

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
        if (count(array_intersect(['*', $name], $this->fillable)) <= 0) {
            return $this;
        }

        $setter = 'set' . Helper::snake2Camel($name);
        if (method_exists($this, $setter)) {
            call_user_func_array([$this, $setter], [$value]);
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }

    public function getAttribute($name)
    {
        if ($this->attributeExists($name)) {
            $getter = 'get' . Helper::snake2Camel($name);
            if (method_exists($this, $getter)) {
                return call_user_func([$this, $getter]);
            } else {
                return $this->attributes[$name];
            }
        }

        return null;
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
        $getter = Helper::snake2Hump($name) . 'Exists';
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        } else {
            return array_key_exists($name, $this->attributes);
        }
    }
}
