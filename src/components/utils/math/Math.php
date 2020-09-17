<?php

namespace SwFwLess\components\utils\math;

use SwFwLess\components\utils\OS;

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

        $osType = OS::type();
        if ($osType === OS::OS_LINUX) {
            $this->ffiPath = __DIR__ . '/ffi/c/linux/libcmath.so';
        } elseif ($osType === OS::OS_DARWIN) {
            $this->ffiPath = __DIR__ . '/ffi/c/darwin/libcmath.so';
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

        if ($numbersCount < ($this->config['sum_ffi_min_count'] ?? 100000)) {
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
