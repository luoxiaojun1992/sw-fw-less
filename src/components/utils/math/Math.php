<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\runtime\php\FFI;

class Math
{
    protected $config = [];
    protected $ffiPath;
    protected $udf;

    /**
     * @param array $config
     * @return Math|static
     */
    public static function create($config = [])
    {
        return new self($config);
    }

    public function __construct($config = [])
    {
        $this->config = $config;

        if (FFI::support()) {
            $osType = OS::type();
            if ($osType === OS::OS_LINUX) {
                $this->ffiPath = __DIR__ . '/ffi/c/linux/libcmath.so';
            } elseif ($osType === OS::OS_DARWIN) {
                $this->ffiPath = __DIR__ . '/ffi/c/darwin/libcmath.so';
            }
        }

        if ($this->ffiPath) {
            $this->udf = $this->createUdf($this->ffiPath);
        }
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef(
            "double ArraySum(double numbers[], int size);" . PHP_EOL .
            "void VectorAdd(float vector1[], float vector2[], int size, float result[]);" . PHP_EOL .
            "void VectorMul(float vector1[], float vector2[], int size, float result[]);" . PHP_EOL .
            "void VectorSqrt(float vector1[], int size, float result[]);" . PHP_EOL .
            "void VectorCmp(float vector1[], float vector2[], int size, float result[]);" . PHP_EOL .
            "void VectorRcp(float vector1[], int size, float result[]);" . PHP_EOL .
            "void VectorDiv(float vector1[], float vector2[], int size, float result[]);" . PHP_EOL .
            "void VectorSub(float vector1[], float vector2[], int size, float result[]);" . PHP_EOL .
            "void VectorAbs(int vector1[], int size, int result[]);" . PHP_EOL .
            "void VectorCeil(float vector1[], int size, float result[]);" . PHP_EOL .
            "void VectorFloor(float vector1[], int size, float result[]);" . PHP_EOL .
            "void VectorRound(float vector1[], int size, float result[]);",
            $ffiPath
        );
    }

    public function createCNumbers($count)
    {
        if (!FFI::support()) {
            return [];
        }
        return \FFI::new('double['.((string)$count).']');
    }

    public function createCFloatNumbers($count)
    {
        if (!FFI::support()) {
            return [];
        }
        return \FFI::new('float['.((string)$count).']');
    }

    public function createCIntNumbers($count)
    {
        if (!FFI::support()) {
            return [];
        }
        return \FFI::new('int['.((string)$count).']');
    }

    public function sum($numbers = null, $numbersCount = null, $cNumbers = null)
    {
        $numbersCount = $numbersCount ?? count($numbers);

        if ($numbersCount < ($this->config['sum_ffi_min_count'] ?? 100000)) {
            return array_sum($numbers);
        }

        if (!FFI::support()) {
            return array_sum($numbers);
        }

        if (!$this->ffiPath) {
            return array_sum($numbers);
        }

        if (is_null($cNumbers)) {
            $cNumbers = static::createCNumbers($numbersCount);
            for ($i = 0; $i < $numbersCount; ++$i) {
                $cNumbers[$i] = $numbers[$i];
            }
        }

        return $this->udf->ArraySum($cNumbers, $numbersCount);
    }

    public function vectorAdd($vector1, $vector2, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] + $vector2[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorAdd($vector1, $vector2, $numbersCount, $result);

        return $result;
    }

    public function vectorSub($vector1, $vector2, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] - $vector2[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorSub($vector1, $vector2, $numbersCount, $result);

        return $result;
    }

    public function vectorMul($vector1, $vector2, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] * $vector2[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorMul($vector1, $vector2, $numbersCount, $result);

        return $result;
    }

    public function vectorDiv($vector1, $vector2, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] / $vector2[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorDiv($vector1, $vector2, $numbersCount, $result);

        return $result;
    }

    public function vectorSqrt($vector1, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = sqrt($vector1[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorSqrt($vector1, $numbersCount, $result);

        return $result;
    }

    public function vectorCmp($vector1, $vector2, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = (($vector1[$i] >= $vector2[$i]) ? NAN : 0);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorCmp($vector1, $vector2, $numbersCount, $result);

        return $result;
    }

    public function vectorRcp($vector1, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = 1 / ($vector1[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorRcp($vector1, $numbersCount, $result);

        return $result;
    }

    public function vectorAbs($vector1, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = abs($vector1[$i]);
            }
            return $result;
        }

        $result = $this->createCIntNumbers($numbersCount);
        $this->udf->VectorAbs($vector1, $numbersCount, $result);

        return $result;
    }

    public function vectorCeil($vector1, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ceil($vector1[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorCeil($vector1, $numbersCount, $result);

        return $result;
    }

    public function vectorFloor($vector1, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = floor($vector1[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorFloor($vector1, $numbersCount, $result);

        return $result;
    }

    public function vectorRound($vector1, $numbersCount)
    {
        if ((!FFI::support()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = round($vector1[$i]);
            }
            return $result;
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $this->udf->VectorRound($vector1, $numbersCount, $result);

        return $result;
    }

    public function bcadd($num1, $num2, $scale = 0)
    {
        return doubleval(bcadd((string)$num1, (string)$num2, $scale));
    }

    public function bcsub($num1, $num2, $scale = 0)
    {
        return doubleval(bcsub((string)$num1, (string)$num2, $scale));
    }

    public function bcmul($num1, $num2, $scale = 0)
    {
        return doubleval(bcmul((string)$num1, (string)$num2, $scale));
    }

    public function bcdiv($num1, $num2, $scale = 0)
    {
        return doubleval(bcdiv((string)$num1, (string)$num2, $scale));
    }
}
