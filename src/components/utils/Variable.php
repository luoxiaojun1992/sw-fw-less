<?php

namespace SwFwLess\components\utils;

/**
 * Class Variable
 * @package SwFwLess\components\utils
 * @deprecated
 */
class Variable
{
    /**
     * @param $variable
     * @param $value
     * @throws \Exception
     */
    public static function assignValue(&$variable, $value)
    {
        \SwFwLess\components\utils\data\structure\variable\Variable::assignValue($variable, $value);
    }

    public static function oneNull(...$vars)
    {
        return \SwFwLess\components\utils\data\structure\variable\Variable::oneNull(...$vars);
    }

    public static function allNull(...$vars)
    {
        return \SwFwLess\components\utils\data\structure\variable\Variable::allNull(...$vars);
    }

    public static function oneNotNull(...$vars)
    {
        return \SwFwLess\components\utils\data\structure\variable\Variable::oneNotNull(...$vars);
    }

    public static function allNotNull(...$vars)
    {
        return \SwFwLess\components\utils\data\structure\variable\Variable::allNotNull(...$vars);
    }
}
