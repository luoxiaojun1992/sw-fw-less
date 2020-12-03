<?php

namespace SwFwLess\components\utils\bitmap\bitarray;

use Lxj\ClosurePHP\Sugars\Scope;
use function Lxj\ClosurePHP\Sugars\Object\newObj;
use function Lxj\ClosurePHP\Sugars\Object\thisObj;

/**
 * @param $slots
 * @return array
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrStaticPublicFuncCreateFromSlots($slots)
{
    $bitIntArr = newObj(
        'SwFwLess\components\utils\bitmap\bitarray\BitIntArr',
        Scope::PRIVATE
    );
    return ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncSetSlots($slots, $bitIntArr);
}

/**
 * @return null
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncGetSlots()
{
    $thisObj = thisObj(func_get_args());
    return $thisObj['props']['slots'];
}

/**
 * @param $slots
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncSetSlots($slots)
{
    $thisObj = thisObj(func_get_args());
    $thisObj['props']['slots'] = $slots;
    return $thisObj;
}

function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncSlotStorage()
{
    return intval(log(PHP_INT_MAX - 1, 2));
}

/**
 * @param $number
 * @return false|float|int
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetSlotIndex($number)
{
    $slotStorage = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncSlotStorage();
    return ceil($number / $slotStorage) - 1;
}

/**
 * @param $number
 * @return int|mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetFractionalAmount($number)
{
    $slotStorage = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncSlotStorage();
    $fractionalAmount = $number % $slotStorage;
    if ($fractionalAmount == 0) {
        $fractionalAmount = $slotStorage;
    }
    return $fractionalAmount;
}

/**
 * @param $number
 * @return int
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetBitmapIndex($number)
{
    $fractionalAmount = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetFractionalAmount($number);
    return 1 << $fractionalAmount - 1;
}

/**
 * @param $number
 * @param $thisObj
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncPut($number, &$thisObj)
{
    $slotIndex = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetSlotIndex($number);
    if (!isset($thisObj['props']['slots'][$slotIndex])) {
        $thisObj['props']['slots'][$slotIndex] = 0;
    }
    $bitMapIndex = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetBitmapIndex($number);
    $thisObj['props']['slots'][$slotIndex] = $thisObj['props']['slots'][$slotIndex] | $bitMapIndex;
    return $thisObj;
}

/**
 * @param $number
 * @param $thisObj
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncAdd($number, &$thisObj)
{
    $hasNumber = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncHas($number, $thisObj);

    if ($hasNumber) {
        throw new \RuntimeException((string) $number . ' existed');
    }

    return ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncPut($number, $thisObj);
}

/**
 * @param $number
 * @param $thisObj
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncDel($number, &$thisObj)
{
    $hasNumber = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncHas($number, $thisObj);
    if (!$hasNumber) {
        throw new \RuntimeException((string) $number . ' not existed');
    }
    $slotIndex = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetSlotIndex($number);
    if (!isset($thisObj['props']['slots'][$slotIndex])) {
        throw new \RuntimeException('Slot of ' . (string) $number . ' not existed');
    }
    $bitMapIndex = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetBitmapIndex($number);
    $thisObj['props']['slots'][$slotIndex] = $thisObj['props']['slots'][$slotIndex] & ~$bitMapIndex;
    return $thisObj;
}

/**
 * @param $number
 * @param $thisObj
 * @return bool
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncHas($number, &$thisObj)
{
    $slotIndex = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetSlotIndex($number);

    if (!isset($thisObj['props']['slots'][$slotIndex])) {
        return false;
    }

    $bitMapIndex = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetBitmapIndex($number);

    return ($thisObj['props']['slots'][$slotIndex] & $bitMapIndex) === $bitMapIndex;
}

/**
 * @param $thisObj
 * @return \Generator
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncIterator(&$thisObj)
{
    $slotStorage = ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncSlotStorage();

    foreach ($thisObj['props']['slots'] as $slotIndex => $slot) {
        for ($i = 1; $i <= $slotStorage; ++$i) {
            $bitMapIndex = 1 << $i - 1;
            if (($bitMapIndex & $slot) === $bitMapIndex) {
                (yield $slotIndex * $slotStorage + $i);
            }
        }
    }
}
