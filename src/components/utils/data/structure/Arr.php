<?php

namespace SwFwLess\components\utils\data\structure;

class Arr
{
    /**
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function safeInArray($needle, $haystack)
    {
        return in_array($needle, $haystack, true);
    }

    /**
     * @param $arr
     * @param $key
     * @return bool
     */
    public static function arrHas($arr, $key)
    {
        return ((!is_array($arr)) || (!is_string($key) && !is_int($key))) ? false : array_key_exists($key, $arr);
    }

    /**
     * @param $arr
     * @param $key
     * @param null $default
     * @return null
     */
    public static function arrGet($arr, $key, $default = null)
    {
        return ((!is_array($arr)) || ((!is_string($key)) && (!is_int($key)))) ? $default :
            (static::arrHas($arr, $key) ? $arr[$key] : $default);
    }

    /**
     * @param $arr
     * @param $key
     * @param $value
     */
    public static function arrSet(&$arr, $key, $value)
    {
        if (!is_array($arr)) {
            return;
        }
        if (!is_string($key) && !is_int($key)) {
            return;
        }

        $arr[$key] = $value;
    }

    public static function arrSetWithoutNull(&$arr, $key, $value)
    {
        if (!is_null($value)) {
            static::arrSet($arr, $key, $value);
        }
    }

    /**
     * @param $arr
     * @param $key
     */
    public static function arrForget(&$arr, $key)
    {
        if (!is_array($arr)) {
            return;
        }
        if (!is_string($key) && !is_int($key)) {
            return;
        }

        unset($arr[$key]);
    }

    /**
     * @param $arr
     * @param $keys
     * @return bool
     */
    public static function nestedArrHas($arr, $keys)
    {
        if (is_array($arr)) {
            //
        } else {
            return false;
        }

        if (is_string($keys)) {
            if (static::arrHas($arr, $keys)) {
                return true;
            }

            $keys = explode('.', $keys);
        } elseif (is_int($keys)) {
            $keys = [$keys];
        } elseif (!is_array($keys)) {
            return false;
        }

        $existed = false;

        $subConfig = $arr;

        foreach ($keys as $key) {
            if (is_array($subConfig) && array_key_exists($key, $subConfig)) {
                $subConfig = $subConfig[$key];
                $existed = true;
            } else {
                $existed = false;
                break;
            }
        }

        return $existed;
    }

    /**
     * @param $arr
     * @param $keys
     * @param null $default
     * @return mixed
     */
    public static function nestedArrGet($arr, $keys, $default = null)
    {
        if (is_array($arr)) {
            //
        } else {
            return $default;
        }

        if (is_string($keys)) {
            if (static::arrHas($arr, $keys)) {
                return static::arrGet($arr, $keys, $default);
            }

            $keys = explode('.', $keys);
        } elseif (is_int($keys)) {
            $keys = [$keys];
        } elseif (!is_array($keys)) {
            return $default;
        }

        $subConfig = $arr;

        foreach ($keys as $key) {
            if (is_array($subConfig) && array_key_exists($key, $subConfig)) {
                $subConfig = $subConfig[$key];
            } else {
                $subConfig = $default;
                break;
            }
        }

        return $subConfig;
    }

    /**
     * @param $arr
     * @param $keys
     * @param $value
     */
    public static function nestedArrSet(&$arr, $keys, $value)
    {
        if (!is_array($arr)) {
            return;
        }

        if (is_string($keys)) {
            if (static::arrHas($arr, $keys)) {
                static::arrSet($arr, $keys, $value);
                return;
            }

            $keys = explode('.', $keys);
        } elseif (is_int($keys)) {
            $keys = [$keys];
        } else {
            if (!is_array($keys)) {
                return;
            }
        }

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (array_key_exists($key, $arr)) {
                if (!is_array($arr[$key])) {
                    return;
                }
            } else {
                $arr[$key] = [];
            }

            $arr = &$arr[$key];
        }

        $arr[array_shift($keys)] = $value;
    }

    /**
     * @param $arr
     * @param $keys
     */
    public static function nestedArrForget(&$arr, $keys)
    {
        if (!is_array($arr)) {
            return;
        }

        if (is_string($keys)) {
            if (static::arrHas($arr, $keys)) {
                static::arrForget($arr, $keys);
                return;
            }

            $keys = explode('.', $keys);
        } elseif (is_int($keys)) {
            $keys = [$keys];
        } else {
            if (!is_array($keys)) {
                return;
            }
        }

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (isset($arr[$key]) && is_array($arr[$key])) {
                $arr = &$arr[$key];
            } else {
                return;
            }
        }

        unset($arr[array_shift($keys)]);
    }

    public static function arrayColumnUnique($arr, $column, $preserveKey = true)
    {
        $columnMap = [];
        foreach ($arr as $key => $val) {
            if (array_key_exists($val[$column], $columnMap)) {
                unset($arr[$key]);
            } else {
                $columnMap[$val[$column]] = true;
            }
        }

        return $preserveKey ? $arr : array_values($arr);
    }

    /**
     * @param array $arr
     * @return array|int[]
     */
    public static function intVal($arr)
    {
        return array_map(function ($item) {return intval($item);}, $arr);
    }

    /**
     * @param array $arr
     * @return array|float[]
     */
    public static function doubleVal($arr)
    {
        return array_map(function ($item) {return doubleval($item);}, $arr);
    }

    /**
     * @param array $arr
     * @return string[]
     */
    public static function stringVal($arr)
    {
        return array_map(function ($item) {return (string)$item;}, $arr);
    }

    /**
     * @param $arr
     * @param $keyColumn
     * @param null $column
     * @return array
     */
    public static function mapping($arr, $keyColumn = null, $column = null)
    {
        return array_column($arr, $column, $keyColumn);
    }

    public static function mappingFilter($arr, $filter, $keyColumn = null, $column = null)
    {
        return static::mapping(array_filter($arr, $filter), $keyColumn, $column);
    }

    public static function dimension($arr)
    {
        $maxSubArrDepth = 0;
        foreach ($arr as $item) {
            if (is_array($item)) {
                $subArrDepth = static::dimension($item);
                if ($subArrDepth > $maxSubArrDepth) {
                    $maxSubArrDepth = $subArrDepth;
                }
            }
        }
        return $maxSubArrDepth + 1;
    }

    public static function topN($arr, $n)
    {
        $priorityQueue = new \SplPriorityQueue();
        $priorityQueue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
        foreach ($arr as $i => $item) {
            $priorityQueue->insert($i, $item);
        }
        $topIndexList = [];
        for ($j = 0; $j < $n; ++$j) {
            $topIndexList[] = $priorityQueue->extract();
        }
        return $topIndexList;
    }

    public static function isAssoc($arr)
    {
        $isAssoc = false;
        foreach ($arr as $key => $item) {
            if (is_string($key)) {
                $isAssoc = true;
                break;
            }
        }
        return $isAssoc;
    }

    public static function swapNums($arr, $index, $anotherIndex)
    {
        $arr[$index] += $arr[$anotherIndex];
        $arr[$anotherIndex] = $arr[$index] - $arr[$anotherIndex];
        $arr[$index] -= $arr[$anotherIndex];
        return $arr;
    }
}
