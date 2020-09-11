<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\traits\Singleton;
use SwFwLess\components\utils\OS;

class Math
{
    use Singleton;

    protected $udfPool = [];

    protected $ffiPath;

    public function __construct()
    {
        $osType = OS::type();
        if ($osType === OS::OS_LINUX) {
            $this->ffiPath = __DIR__ . '/ffi/c/linux/libcmath.so';
        } elseif ($osType === OS::OS_DARWIN) {
            $this->ffiPath = __DIR__ . '/ffi/c/darwin/libcmath.so';
        }

        if ($this->ffiPath) {
            //TODO config
            for ($i = 0; $i < 10; ++$i) {
                $this->udfPool[] = $this->createUdf($this->ffiPath);
            }
        }
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef("double ArraySum(double numbers[], int size);", $ffiPath);
    }

    public function createCNumbers($count)
    {
        return \FFI::new('double['.((string)$count).']');
    }

    public function sum($numbers = null, $numbersCount = null, $cNumbers = null)
    {
        if (version_compare(PHP_VERSION, '7.4.0') < 0) {
            return array_sum($numbers);
        }

        if (!$this->ffiPath) {
            return array_sum($numbers);
        }

        $newUdf = false;
        if (!($udf = array_pop($this->udfPool))) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $numbersCount = $numbersCount ?? count($numbers);

        //TODO config
        if ($numbersCount < 100000) {
            return array_sum($numbers);
        }

        if (is_null($cNumbers)) {
            $cNumbers = static::createCNumbers($numbersCount);
            for ($i = 0; $i < $numbersCount; ++$i) {
                $cNumbers[$i] = $numbers[$i];
            }
        }

        $result = $udf->ArraySum($cNumbers, $numbersCount);

        if (!$newUdf) {
            array_push($this->udfPool, $udf);
        }

        return $result;
    }
}
