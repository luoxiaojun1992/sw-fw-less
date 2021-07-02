<?php

namespace SwFwLess\facades;

/**
 * Class Math
 *
 * @method static createCNumbers($count)
 * @method static createCFloatNumbers($count)
 * @method static sum($numbers = null, $numbersCount = null, $cNumbers = null)
 * @method static vectorAdd($vector1, $vector2, $numbersCount)
 * @method static vectorMul($vector1, $vector2, $numbersCount)
 * @method static vectorSqrt($vector1, $numbersCount)
 * @method static vectorCmp($vector1, $vector2, $numbersCount)
 * @method static vectorRcp($vector1, $numbersCount)
 * @method static vectorDiv($vector1, $vector2, $numbersCount)
 * @method static vectorSub($vector1, $vector2, $numbersCount)
 * @method static vectorAbs($vector1, $numbersCount)
 * @method static vectorCeil($vector1, $numbersCount)
 * @method static vectorFloor($vector1, $numbersCount)
 *
 * @package SwFwLess\facades
 */
class Math extends AbstractFacade
{
    /**
     * @return \SwFwLess\components\traits\Singleton|\SwFwLess\components\utils\math\Math|null
     * @throws \Exception
     */
    protected static function getAccessor()
    {
        return \SwFwLess\components\utils\math\Math::create();
    }
}
