<?php

namespace SwFwLess\components\utils;

class Arr
{
    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function safeInArray($needle, $haystack)
    {
        return \SwFwLess\components\utils\data\structure\Arr::safeInArray($needle, $haystack);
    }

    /**
     * @param $arr
     * @param $key
     * @return bool
     */
    public static function arrHas($arr, $key)
    {
        return \SwFwLess\components\utils\data\structure\Arr::arrHas($arr, $key);
    }

    /**
     * @param $arr
     * @param $key
     * @param null $default
     * @return null
     */
    public static function arrGet($arr, $key, $default = null)
    {
        return \SwFwLess\components\utils\data\structure\Arr::arrGet($arr, $key, $default);
    }

    /**
     * @param $arr
     * @param $key
     * @param $value
     */
    public static function arrSet(&$arr, $key, $value)
    {
        \SwFwLess\components\utils\data\structure\Arr::arrSet($arr, $key, $value);
    }

    /**
     * @param $arr
     * @param $key
     */
    public static function arrForget(&$arr, $key)
    {
        \SwFwLess\components\utils\data\structure\Arr::arrForget($arr, $key);
    }

    /**
     * @param $arr
     * @param $keys
     * @return bool
     */
    public static function nestedArrHas($arr, $keys)
    {
        return \SwFwLess\components\utils\data\structure\Arr::nestedArrHas($arr, $keys);
    }

    /**
     * @param $arr
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public static function nestedArrGet($arr, $keys, $default = null)
    {
        return \SwFwLess\components\utils\data\structure\Arr::nestedArrGet($arr, $keys, $default);
    }

    /**
     * @param $arr
     * @param $keys
     * @param $value
     */
    public static function nestedArrSet(&$arr, $keys, $value)
    {
        \SwFwLess\components\utils\data\structure\Arr::nestedArrSet($arr, $keys, $value);
    }

    /**
     * @param $arr
     * @param $keys
     */
    public static function nestedArrForget(&$arr, $keys)
    {
        \SwFwLess\components\utils\data\structure\Arr::nestedArrForget($arr, $keys);
    }

    public static function arrayColumnUnique($arr, $column, $preserveKey = true)
    {
        return \SwFwLess\components\utils\data\structure\Arr::arrayColumnUnique($arr, $column, $preserveKey);
    }

    /**
     * @param array $arr
     * @return array|int[]
     */
    public static function intVal($arr)
    {
        return \SwFwLess\components\utils\data\structure\Arr::intVal($arr);
    }

    /**
     * @param array $arr
     * @return array|float[]
     */
    public static function doubleVal($arr)
    {
        return \SwFwLess\components\utils\data\structure\Arr::doubleVal($arr);
    }

    /**
     * @param array $arr
     * @return string[]
     */
    public static function stringVal($arr)
    {
        return \SwFwLess\components\utils\data\structure\Arr::stringVal($arr);
    }

    /**
     * @param $arr
     * @param $keyColumn
     * @param null $column
     * @return array
     */
    public static function mapping($arr, $keyColumn, $column = null)
    {
        return \SwFwLess\components\utils\data\structure\Arr::mapping($arr, $keyColumn, $column);
    }
}
