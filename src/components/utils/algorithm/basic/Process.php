<?php

namespace SwFwLess\components\utils\algorithm;

class Process
{
    /**
     * @param $callback
     * @param $endIndex
     * @param int $startIndex
     * @param false $haltOnFalse
     * @return bool
     */
    public static function loop($callback, $endIndex, $startIndex = 0, $haltOnFalse = false)
    {
        for ($index = $startIndex; $index <= $endIndex; ++$startIndex) {
            $result = call_user_func($callback, $index);
            if ($haltOnFalse) {
                if ($result === false) {
                    return false;
                }
            }
        }

        return true;
    }
}
