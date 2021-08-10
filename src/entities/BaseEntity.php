<?php

namespace SwFwLess\entities;

/**
 * Class BaseEntity
 * @package SwFwLess\entities
 * @deprecated
 */
class BaseEntity
{
    protected $attributes;

    protected function __setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __set($name, $value)
    {
        $this->__setAttribute($name, $value);
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public static function createFromAttributes($attributes)
    {
        return (new static)->setAttributes($attributes);
    }

    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value) {
            $upperName = ucfirst($name);
            $setter = 'set' . $upperName;
            if (method_exists($this, $setter)) {
                call_user_func([$this, $setter], $value);
            } else {
                $this->__setAttribute($name, $value);
            }
        }
        return $this;
    }

    public function getAttributes()
    {
        $attributes = $this->attributes;
        foreach ($attributes as $name => $value) {
            $upperName = ucfirst($name);
            $setter = 'get' . $upperName;
            if (method_exists($this, $setter)) {
                $attributes[$name] = call_user_func([$this, $setter], $value);
            }
        }
        return $attributes;
    }
}
