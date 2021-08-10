<?php

namespace SwFwLess\models\traits;

trait ModelAttributes
{
    protected $originalAttributes = [];
    protected $fillable = ['*'];

    /**
     * @param $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->setData($attributes);

        return $this;
    }

    public function getAttributes()
    {
        return $this->getData();
    }

    public function setAttribute($name, $value)
    {
        if (count(array_intersect(['*', $name], $this->fillable)) <= 0) {
            return $this;
        }

        $this->set($name, $value);
        $this->justSaved = false;
        return $this;
    }

    public function getAttribute($name)
    {
        return $this->get($name);
    }

    public function removeAttribute($name)
    {
        $this->forget($name);
        $this->justSaved = false;
        return $this;
    }

    public function attributeExists($name)
    {
        return $this->has($name);
    }
}
