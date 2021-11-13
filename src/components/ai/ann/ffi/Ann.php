<?php

namespace SwFwLess\components\ai\ann\ffi;

use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\Runtime;

class Ann
{
    protected $config = [];
    protected $ffiPath;
    protected $udf;

    /**
     * @param array $config
     * @return Ann|static
     */
    public static function create($config = [])
    {
        return new self($config);
    }

    public function __construct($config = [])
    {
        $this->config = $config;

        if (Runtime::supportFFI()) {
            $osType = OS::type();
            if ($osType === OS::OS_LINUX) {
                $this->ffiPath = __DIR__ . '/c/build/linux/libcann.so';
            } elseif ($osType === OS::OS_DARWIN) {
                $this->ffiPath = __DIR__ . '/c/build/darwin/libcann.so';
            }
        }

        if ($this->ffiPath) {
            $this->udf = $this->createUdf($this->ffiPath);
        }
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef(
            "int TrainAndPredictOnce(const double** input, const double** output, const int sample_total, const double* test_input, double* test_output, const int inputs, const int hidden_layers, const int hidden, const int outputs, const double learning_rate, const int epochs);",
            $ffiPath
        );
    }

    public function trainAndPredictOnce()
    {
        //TODO
    }
}
