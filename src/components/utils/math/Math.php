<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\swoole\Scheduler;
use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\Runtime;

class Math
{
    protected $config = [];
    protected $udfPool = [];
    protected $ffiPath;

    /** @var static */
    private static $instance;

    /**
     * @param array $config
     * @return Math|static
     */
    public static function create($config = [])
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($config);
    }

    public function __construct($config = [])
    {
        $this->config = $config;

        if (Runtime::supportFFI()) {
            $osType = OS::type();
            if ($osType === OS::OS_LINUX) {
                $this->ffiPath = __DIR__ . '/ffi/c/linux/libcmath.so';
            } elseif ($osType === OS::OS_DARWIN) {
                $this->ffiPath = __DIR__ . '/ffi/c/darwin/libcmath.so';
            }
        }

        if ($this->ffiPath) {
            $poolSize = $this->config['pool_size'] ?? 10;
            for ($i = 0; $i < $poolSize; ++$i) {
                $this->udfPool[] = $this->createUdf($this->ffiPath);
            }
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
            "void VectorFloor(float vector1[], int size, float result[]);",
            $ffiPath
        );
    }

    public function createCNumbers($count)
    {
        return \FFI::new('double['.((string)$count).']');
    }

    public function createCFloatNumbers($count)
    {
        return \FFI::new('float['.((string)$count).']');
    }

    public function createCIntNumbers($count)
    {
        return \FFI::new('int['.((string)$count).']');
    }

    public function sum($numbers = null, $numbersCount = null, $cNumbers = null)
    {
        $numbersCount = $numbersCount ?? count($numbers);

        if ($numbersCount < ($this->config['sum_ffi_min_count'] ?? 100000)) {
            return array_sum($numbers);
        }

        if (!Runtime::supportFFI()) {
            return array_sum($numbers);
        }

        if (!$this->ffiPath) {
            return array_sum($numbers);
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        if (is_null($cNumbers)) {
            $cNumbers = static::createCNumbers($numbersCount);
            for ($i = 0; $i < $numbersCount; ++$i) {
                $cNumbers[$i] = $numbers[$i];
            }
        }

        $result = $udf->ArraySum($cNumbers, $numbersCount);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorAdd($vector1, $vector2, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] + $vector2[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorAdd($vector1, $vector2, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorSub($vector1, $vector2, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] - $vector2[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorSub($vector1, $vector2, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorMul($vector1, $vector2, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] * $vector2[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorMul($vector1, $vector2, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorDiv($vector1, $vector2, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ($vector1[$i] / $vector2[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorDiv($vector1, $vector2, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorSqrt($vector1, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = sqrt($vector1[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorSqrt($vector1, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorCmp($vector1, $vector2, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = (($vector1[$i] >= $vector2[$i]) ? NAN : 0);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorCmp($vector1, $vector2, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorRcp($vector1, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = 1 / ($vector1[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorRcp($vector1, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorAbs($vector1, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = abs($vector1[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCIntNumbers($numbersCount);
        $udf->VectorAbs($vector1, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorCeil($vector1, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = ceil($vector1[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorCeil($vector1, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }

    public function vectorFloor($vector1, $numbersCount)
    {
        if ((!Runtime::supportFFI()) || (!$this->ffiPath)) {
            $result = [];
            for ($i = 0; $i < $numbersCount; ++$i) {
                $result[$i] = floor($vector1[$i]);
            }
            return $result;
        }

        $newUdf = false;
        $udf = Scheduler::withoutPreemptive(function () {
            return array_pop($this->udfPool);
        });
        if (!$udf) {
            $newUdf = true;
            $udf = $this->createUdf($this->ffiPath);
        }

        $result = $this->createCFloatNumbers($numbersCount);
        $udf->VectorFloor($vector1, $numbersCount, $result);

        if (!$newUdf) {
            Scheduler::withoutPreemptive(function () use ($udf) {
                array_push($this->udfPool, $udf);
            });
        }

        return $result;
    }
}
