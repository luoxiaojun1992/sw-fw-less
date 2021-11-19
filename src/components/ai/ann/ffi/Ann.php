<?php

namespace SwFwLess\components\ai\ann\ffi;

use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\runtime\PHPRuntime;

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

        if (PHPRuntime::supportFFI()) {
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

    public function createCDoubleNumbers($count)
    {
        return \FFI::new('double[' . ((string)$count) . ']');
    }

    public function createCConstDoubleNumbers($count)
    {
        return \FFI::new('const double[' . ((string)$count) . ']');
    }

    public function createCDoubleNumbersPointerArray($count)
    {
        return \FFI::new('double* [' . ((string)$count) . ']');
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef(
            "int TrainAndPredictOnce(const double** input, const double** output, const int sample_total, const double* test_input, double* test_output, const int inputs, const int hidden_layers, const int hidden, const int outputs, const double learning_rate, const int epochs);",
            $ffiPath
        );
    }

    public function trainAndPredictOnce(
        $input, $output, $sampleTotal, $testInput, $inputs, $hiddenLayers, $hidden,
        $outputs, $learningRate, $epochs
    )
    {
        $cInput = $this->createCDoubleNumbersPointerArray($sampleTotal);
        foreach ($input as $row => $numbers) {
            $cInputNumbersVarName = 'cInputNumbers' . $row;
            $$cInputNumbersVarName = $this->createCDoubleNumbers($inputs);
            foreach ($numbers as $col => $number) {
                $$cInputNumbersVarName[$col] = $number;
            }
            $cInput[$row] = \FFI::cast('double*', \FFI::addr($$cInputNumbersVarName));
        }

        $cOutput = $this->createCDoubleNumbersPointerArray($sampleTotal);
        foreach ($output as $row => $numbers) {
            $cOutputNumbersVarName = 'cOutputNumbers' . $row;
            $$cOutputNumbersVarName = $this->createCDoubleNumbers($outputs);
            foreach ($numbers as $col => $number) {
                $$cOutputNumbersVarName[$col] = $number;
            }
            $cOutput[$row] = \FFI::cast('double*', \FFI::addr($$cOutputNumbersVarName));
        }

        $cTestInput = $this->createCDoubleNumbers($inputs);
        foreach ($testInput as $i => $number) {
            $cTestInput[$i] = $number;
        }

        $cTestOutput = $this->createCDoubleNumbers($outputs);

        $this->udf->TrainAndPredictOnce(
            $cInput, $cOutput, $sampleTotal, $cTestInput,
            $cTestOutput, $inputs, $hiddenLayers, $hidden,
            $outputs, $learningRate, $epochs
        );

        return $cTestOutput;
    }
}
