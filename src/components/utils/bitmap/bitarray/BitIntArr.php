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
    public function put($number)
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
     */
    public function add($number)
    {
        if ($this->has($number)) {
            throw new \RuntimeException(((string)$number) . ' existed');
        }

        $this->put($number);
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

    /**
     * @return \Generator
     */
    public function iterator()
    {
        $slotStorage = $this->slotStorage();
        foreach ($this->slots as $slotIndex => $slot) {
            for ($i = 1; $i <= $slotStorage; ++$i) {
                $bitMapIndex = (1 << ($i - 1));
                if (($bitMapIndex & $slot) === $bitMapIndex) {
                    yield (($slotIndex * $slotStorage) + $i);
                }
            }
        }
    }
}
