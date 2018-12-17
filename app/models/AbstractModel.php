<?php

namespace App\models;

use App\components\Helper;

abstract class AbstractModel implements \JsonSerializable
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
