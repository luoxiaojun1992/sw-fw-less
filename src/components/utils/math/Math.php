<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\utils\OS;

class Math
{
    public static function sum($numbers, $numbersCount = null)
    {
        if (version_compare(PHP_VERSION, '7.4.0') < 0) {
            return array_sum($numbers);
        }

        $numbersCount = $numbersCount ?? count($numbers);

        if ($numbersCount < 100000) {
            return array_sum($numbers);
        }

        $ffiPath = '';

        //TODO move to construct method
        $osType = OS::type();
        if ($osType === OS::OS_LINUX) {
            $ffiPath = __DIR__ . '/ffi/c/linux/libcmath.so';
        } elseif ($osType === OS::OS_DARWIN) {
            $ffiPath = __DIR__ . '/ffi/c/darwin/libcmath.so';
        }

        if (!$ffiPath) {
            return array_sum($numbers);
        }

        $udf = \FFI::cdef("double ArraySum(double numbers[], int size);", $ffiPath);
        $arr = \FFI::new('double[' . ((string)$numbersCount) . ']');
        for ($i = 0; $i < $numbersCount; ++$i) {
            $arr[$i] = $numbers[$i];
        }

        return $udf->ArraySum($arr, $numbersCount);
    }
}
