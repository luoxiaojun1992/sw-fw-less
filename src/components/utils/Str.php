<?php

namespace SwFwLess\components\utils;

class Str
{
    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle === '') {
                return true;
            }

            if (mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
