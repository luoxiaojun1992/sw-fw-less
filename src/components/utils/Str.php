<?php

namespace SwFwLess\components\utils;

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
        foreach ((array) $needles as $needle) {
            if ($needle === '') {
                return true;
            }

            if ($caseSensitive && (mb_strpos($haystack, $needle, $offset) !== false)) {
                return true;
            } elseif ((!$caseSensitive) && (mb_stripos($haystack, $needle, $offset) !== false)) {
                return true;
            }
        }

        return false;
    }

    public static function split($str, $separator)
    {
        if ($separator === '') {
            return preg_split('/(?<!^)(?!$)/', $str);
        }
        return explode($separator, $str);
    }
}
