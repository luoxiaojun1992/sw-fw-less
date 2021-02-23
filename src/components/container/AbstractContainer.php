<?php

namespace SwFwLess\components\container;

use SwFwLess\components\utils\Arr;

class AbstractContainer
{
    protected $data;

    public function set($id, $res)
    {
        Arr::nestedArrSet($this->data, $id, $res);
        return $this;
    }

    public function get($id)
    {
        return Arr::nestedArrGet($this->data, $id, null);
    }

    public function has($id)
    {
        return Arr::nestedArrHas($this->data, $id);
    }

    public function forget($id)
    {
        Arr::nestedArrForget($this->data, $id);
        return $this;
    }
}
