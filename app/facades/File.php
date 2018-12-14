<?php

namespace App\facades;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class Es
 *
 * @method static string basePath()
 * @method static string path($relativePath)
 * @method static string appPath()
 * @method static string storagePath()
 * @method static Filesystem prepare($writeFlags = LOCK_EX, $linkHandling = Local::DISALLOW_LINKS, $permissions = [])
 * @package App\facades
 */
class File extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\storage\File::create();
    }
}
