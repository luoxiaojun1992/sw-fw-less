<?php

namespace SwFwLess\components\utils;

/**
 * Class BitIntArr
 *
 * {@inheritDoc}
 *
 * Slot index starts from 0.
 *
 * Bitmap index starts from 1.
 *
 * @package SwFwLess\components\utils
 */
class BitIntArr
{
    protected $slots;

    //todo unit test

    protected function slotStorage()
    {
        return intval(log(PHP_INT_MAX - 1, 2));
    }

    /**
     * {@inheritDoc}
     *
     * Slot index starts from 0.
     *
     * @param $number
     * @return false|float|int
     */
    protected function getSlotIndex($number)
    {
        return ceil($number / $this->slotStorage()) - 1;
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

    /**
     * {@inheritDoc}
     *
     * Bitmap index starts from 1.
     *
     * @param $number
     * @return int
     */
    protected function getBitmapIndex($number)
    {
        return 1 << ($this->getFractionalAmount($number) - 1);
    }

    /**
     * @param $number
     */
    public function set($number)
    {
        $slotIndex = $this->getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            $this->slots[$slotIndex] = 0;
        }

        $bitMapIndex = $this->getBitmapIndex($number);
        $this->slots[$slotIndex] = $this->slots[$slotIndex] | $bitMapIndex;
    }

    /**
     * @param $number
     * @return bool
     */
    public function has($number)
    {
        $slotIndex = $this->getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            return false;
        }

        $bitMapIndex = $this->getBitmapIndex($number);
        return ($this->slots[$slotIndex] & $bitMapIndex) === $bitMapIndex;
    }

    public function iterator()
    {
        //todo
    }
}
