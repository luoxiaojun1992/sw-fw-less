<?php

namespace SwFwLess\facades;

/**
 * class Ann
 *
 * @method static trainAndPredictOnce($input, $output, $sampleTotal, $testInput, $inputs, $hiddenLayers, $hidden, $outputs, $learningRate, $epochs)
 *
 * @package SwFwLess\facades
 */
class Ann extends AbstractFacade
{
    /**
     * @return \SwFwLess\components\ai\ann\ffi\Ann|null
     * @throws \Exception
     */
    protected static function getAccessor()
    {
        return \SwFwLess\components\ai\ann\ffi\Ann::create([]);
    }
}
