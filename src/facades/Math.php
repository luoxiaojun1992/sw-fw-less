<?php

namespace SwFwLess\facades;

/**
 * Class Math
 *
 * @method static createCNumbers($count)
 * @method static sum($numbers = null, $numbersCount = null, $cNumbers = null)
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
