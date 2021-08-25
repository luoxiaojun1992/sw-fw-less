<?php

namespace SwFwLess\components\utils;

use League\Flysystem\Adapter\Local;
use SwFwLess\facades\File;

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

    public static function size($filePath)
    {
        return (new \SplFileInfo($filePath))->getSize();
    }

    public static function mimetype($filePath)
    {
        return File::prepare(
            LOCK_SH,
            Local::DISALLOW_LINKS,
            [],
            dirname($filePath)
        )->getMimetype(basename($filePath));
    }
}
