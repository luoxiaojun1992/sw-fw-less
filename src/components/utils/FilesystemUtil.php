<?php

namespace SwFwLess\components\utils;

class FilesystemUtil
{
    /**
     * @param $pattern
     * @return array
     * @throws \Exception
     */
    public static function scanDir($pattern)
    {
        $fileOrDirList = glob($pattern, GLOB_ERR);

        if ($fileOrDirList === false) {
            throw new \Exception('Scan dir error');
        }

        return $fileOrDirList;
    }
}
