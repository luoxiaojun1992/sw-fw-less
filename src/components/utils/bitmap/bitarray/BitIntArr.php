<?php

namespace SwFwLess\components\utils\bitmap\bitarray;

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
    /** @var array  */
    protected $slots = [];

    /**
     * @param array $slots
     * @return BitIntArr
     */
    public static function createFromSlots(array $slots = [])
    {
        return (new static())->setSlots($slots);
    }

    /**
     * @return array
     */
    public function getSlots(): array
    {
        return $this->slots;
    }

    /**
     * @param array $slots
     * @return $this
     */
    public function setSlots(array $slots): self
    {
        $this->slots = $slots;
        return $this;
    }

    protected static function slotStorage()
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
    protected static function getSlotIndex($number)
    {
        return ceil($number / static::slotStorage()) - 1;
    }

    protected static function getFractionalAmount($number)
    {
        $slotStorage = static::slotStorage();

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
    protected static function getBitmapIndex($number)
    {
        return 1 << (static::getFractionalAmount($number) - 1);
    }

    /**
     * @param $number
     */
    public function put($number)
    {
        $slotIndex = static::getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            $this->slots[$slotIndex] = 0;
        }

        $bitMapIndex = static::getBitmapIndex($number);
        $this->slots[$slotIndex] = ($this->slots[$slotIndex] | $bitMapIndex);
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
     */
    public function del($number)
    {
        if (!$this->has($number)) {
            throw new \RuntimeException(((string)$number) . ' not existed');
        }

        $slotIndex = static::getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            throw new \RuntimeException(('Slot of ' . (string)$number) . ' not existed');
        }

        $bitMapIndex = static::getBitmapIndex($number);
        $this->slots[$slotIndex] = ($this->slots[$slotIndex] & (~$bitMapIndex));
    }

    /**
     * @param $number
     * @return bool
     */
    public function has($number)
    {
        $slotIndex = static::getSlotIndex($number);
        if (!isset($this->slots[$slotIndex])) {
            return false;
        }

        $bitMapIndex = static::getBitmapIndex($number);
        return ($this->slots[$slotIndex] & $bitMapIndex) > 0;
    }

    /**
     * @return \Generator
     */
    public function iterator()
    {
        $slotStorage = static::slotStorage();
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
