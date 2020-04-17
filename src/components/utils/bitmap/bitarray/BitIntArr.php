<?php

namespace SwFwLess\components\utils;

class BitIntArr
{
    protected $slots;

    protected function slotStorage()
    {
        return intval(log(PHP_INT_MAX - 1, 2));
    }

    public function set($number)
    {
        $slotStorage = $this->slotStorage();

        $slotIndex = ceil($number / $slotStorage);
        if (!isset($this->slots[$slotIndex])) {
            $this->slots[$slotIndex] = 0;
        }

        $fractionalAmount = $number % $slotStorage;
        if ($fractionalAmount == 0) {
            $fractionalAmount = $slotStorage;
        }

        $bitMap = 1 << ($fractionalAmount - 1);
        $this->slots[$slotIndex] = $this->slots[$slotIndex] & $bitMap;
    }

    public function has($number)
    {

    }
}
