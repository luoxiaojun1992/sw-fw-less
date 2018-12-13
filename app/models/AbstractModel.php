<?php

namespace App\models;

use App\components\Helper;

abstract class AbstractModel
{
    private $attributes = [];

    /**
     * @param $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $setter = 'set' . Helper::snake2Camel($name);
            if (method_exists($this, $setter)) {
                call_user_func_array([$this, $setter], [$value]);
            } else {
                $this->attributes[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
