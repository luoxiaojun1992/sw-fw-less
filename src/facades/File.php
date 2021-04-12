<?php

namespace SwFwLess\facades;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use SwFwLess\components\storage\Storage;

/**
 * Class File
 *
 * @method static string basePath()
 * @method static string path($relativePath)
 * @method static string appPath()
 * @method static string storagePath()
 * @method static Filesystem prepare($writeFlags = LOCK_EX, $linkHandling = Local::DISALLOW_LINKS, $permissions = [])
 * @package SwFwLess\facades
 */
class File extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Storage::file();
    }
}
