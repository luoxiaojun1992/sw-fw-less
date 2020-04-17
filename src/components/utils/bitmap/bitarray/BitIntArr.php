<?php

namespace SwFwLess\components\utils;

class BitIntArr
{
    protected $slots;

    //todo unit test

    protected function slotStorage()
    {
        return intval(log(PHP_INT_MAX - 1, 2));
    }

    protected function getSlotIndex($number)
    {
        return ceil($number / $this->slotStorage());
    }

    protected function getFractionalAmount($number)
    {
        $slotStorage = $this->slotStorage();

        $fractionalAmount = $number % $slotStorage;
        if ($fractionalAmount == 0) {
            $fractionalAmount = $slotStorage;
        }

        return $fractionalAmount;
    }

    public function set($number)
    {
        $slotIndex = $this->getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            $this->slots[$slotIndex] = 0;
        }

        $fractionalAmount = $this->getFractionalAmount($number);

        $bitNumber = 1 << ($fractionalAmount - 1);
        $this->slots[$slotIndex] = $this->slots[$slotIndex] | $bitNumber;
    }

    public function has($number)
    {
        $slotIndex = $this->getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            return false;
        }

        $fractionalAmount = $this->getFractionalAmount($number);

        $bitNumber = 1 << ($fractionalAmount - 1);

        return ($this->slots[$slotIndex] & $bitNumber) === $bitNumber;
    }
}
