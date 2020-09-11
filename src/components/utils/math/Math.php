<?php

namespace SwFwLess\components\utils\math;

class Math
{
    public static function sum($numbers, $numbersCount = null)
    {
        $numbersCount = $numbersCount ?? count($numbers);
        $udf = \FFI::cdef("double ArraySum(double numbers[], int size);", __DIR__ . '/ffi/c/libcmath.so');
        $arr = \FFI::new('double[' . ((string)$numbersCount) . ']');
        for ($i = 0; $i < $numbersCount; ++$i) {
            $arr[$i] = $numbers[$i];
        }

        return $udf->ArraySum($arr, $numbersCount);
    }
}
