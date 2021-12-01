<?php

namespace SwFwLess\components\utils;

/**
 * @deprecated
 */
class Str
{
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @param  int $offset
     * @param  bool $caseSensitive
     * @return bool
     */
    public static function contains($haystack, $needles, $offset = 0, $caseSensitive = true)
    {
        return \SwFwLess\components\utils\data\structure\Str::contains(
            $haystack, $needles, $offset, $caseSensitive
        );
    }

    public static function split($str, $separator)
    {
        return \SwFwLess\components\utils\data\structure\Str::split($str, $separator);
    }
}
