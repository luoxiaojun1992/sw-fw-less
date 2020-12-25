<?php

class TestRedis extends \SwFwLess\components\redis\RedisWrapper
{
    protected $mockResponseArr;

    /**
     * @param mixed $mockResponseArr
     * @return $this
     */
    public function setMockResponseArr($mockResponseArr)
    {
        $this->mockResponseArr = $mockResponseArr;
        return $this;
    }

    public function __call($name, $arguments)
    {
        return array_shift($this->mockResponseArr);
    }
}
