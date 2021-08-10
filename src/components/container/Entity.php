<?php

namespace SwFwLess\components\container;

class Entity extends Container
{
    public function setAttribute($name, $value)
    {
        $this->set($name, $value);
    }

    public function getAttribute($name)
    {
        return $this->get($name);
    }

    public function forgetAttribute($name)
    {
        $this->forget($name);
    }
}
