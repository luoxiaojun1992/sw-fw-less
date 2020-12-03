<?php

namespace SwFwLess\components\utils\bitmap\bitarray;

use Lxj\ClosurePHP\Sugars\Scope;
use function Lxj\ClosurePHP\Sugars\Object\access;
use function Lxj\ClosurePHP\Sugars\Object\call;
use function Lxj\ClosurePHP\Sugars\Object\get;
use function Lxj\ClosurePHP\Sugars\Object\newObj;
use function Lxj\ClosurePHP\Sugars\Object\set;
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
    call(
        $bitIntArr,
        'setSlots',
        Scope::PRIVATE,
        [$slots],
        false,
        null
    );
    return $bitIntArr;
}

/**
 * @return null
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncGetSlots()
{
    return get(
        thisObj(func_get_args()),
        'slots',
        Scope::PRIVATE
    );
}

/**
 * @param $slots
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncSetSlots($slots)
{
    $thisObj = thisObj(func_get_args());
    set(
        $thisObj,
        'slots',
        $slots,
        Scope::PRIVATE
    );
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
    $slotStorage = call(
        thisObj(func_get_args()),
        'slotStorage',
        Scope::PRIVATE,
        [],
        false,
        null
    );
    return ceil($number / $slotStorage) - 1;
}

/**
 * @param $number
 * @return int|mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstanceProtectedFuncGetFractionalAmount($number)
{
    $slotStorage = call(
        thisObj(func_get_args()),
        'slotStorage',
        Scope::PRIVATE,
        [],
        false,
        null
    );
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
    $fractionalAmount = call(
        thisObj(func_get_args()),
        'getFractionalAmount',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );
    return 1 << $fractionalAmount - 1;
}

/**
 * @param $number
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncPut($number)
{
    $thisObj = thisObj(func_get_args());
    $slotIndex = call(
        $thisObj,
        'getSlotIndex',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );
    access(
        $thisObj,
        'slots',
        function(&$obj) use ($slotIndex) {
            if (!isset($obj['props']['slots'][$slotIndex])) {
                $obj['props']['slots'][$slotIndex] = 0;
            }
        },
        Scope::PRIVATE
    );
    $bitMapIndex = call(
        $thisObj,
        'getBitmapIndex',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );
    access(
        $thisObj,
        'slots',
        function(&$obj) use ($slotIndex, $bitMapIndex) {
            if (!isset($obj['props']['slots'][$slotIndex])) {
                $obj['props']['slots'][$slotIndex] = 0;
            }
            $obj['props']['slots'][$slotIndex] = $obj['props']['slots'][$slotIndex] | $bitMapIndex;
        },
        Scope::PRIVATE
    );
    return $thisObj;
}

/**
 * @param $number
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncAdd($number)
{
    $thisObj = thisObj(func_get_args());

    $hasNumber = call(
        $thisObj,
        'has',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );

    if ($hasNumber) {
        throw new \RuntimeException((string) $number . ' existed');
    }

    return call(
        $thisObj,
        'put',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );
}

/**
 * @param $number
 * @return mixed
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncDel($number)
{
    $thisObj = thisObj(func_get_args());

    $hasNumber = call(
        $thisObj,
        'has',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );

    if (!$hasNumber) {
        throw new \RuntimeException((string) $number . ' not existed');
    }
    $slotIndex = call(
        $thisObj,
        'getSlotIndex',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );
    access(
        $thisObj,
        'slots',
        function (&$obj) use ($slotIndex, $number) {
            if (!isset($obj['props']['slots'][$slotIndex])) {
                throw new \RuntimeException('Slot of ' . (string) $number . ' not existed');
            }
        },
        Scope::PRIVATE
    );
    $bitMapIndex = call(
        $thisObj,
        'getBitmapIndex',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );
    access(
        $thisObj,
        'slots',
        function(&$obj) use ($slotIndex, $bitMapIndex) {
            $obj['props']['slots'][$slotIndex] = $obj['props']['slots'][$slotIndex] & ~$bitMapIndex;
        },
        Scope::PRIVATE
    );
    return $thisObj;
}

/**
 * @param $number
 * @return bool
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncHas($number)
{
    $thisObj = thisObj(func_get_args());

    $slotIndex = call(
        $thisObj,
        'getSlotIndex',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );

    $hasSlot = access(
        $thisObj,
        'slots',
        function (&$obj) use ($slotIndex) {
            if (!isset($obj['props']['slots'][$slotIndex])) {
                return false;
            }
            return true;
        },
        Scope::PRIVATE
    );

    if (!$hasSlot) {
        return false;
    }

    $bitMapIndex = call(
        $thisObj,
        'getBitmapIndex',
        Scope::PRIVATE,
        [$number],
        false,
        null
    );

    return access(
        $thisObj,
        'slots',
        function (&$obj) use ($slotIndex, $bitMapIndex) {
            return ($obj['props']['slots'][$slotIndex] & $bitMapIndex) === $bitMapIndex;
        },
        Scope::PRIVATE
    );
}

/**
 * @return \Generator
 * @throws \Exception
 */
function ClassSwFwLess_components_utils_bitmap_bitarray_BitIntArrInstancePublicFuncIterator()
{
    $thisObj = thisObj(func_get_args());

    $slotStorage = call(
        $thisObj,
        'slotStorage',
        Scope::PRIVATE,
        [],
        false,
        null
    );

    return access(
        $thisObj,
        'slots',
        function (&$obj) use ($slotStorage) {
            foreach ($obj['props']['slots'] as $slotIndex => $slot) {
                for ($i = 1; $i <= $slotStorage; ++$i) {
                    $bitMapIndex = 1 << $i - 1;
                    if (($bitMapIndex & $slot) === $bitMapIndex) {
                        (yield $slotIndex * $slotStorage + $i);
                    }
                }
            }
        },
        Scope::PRIVATE
    );
}
