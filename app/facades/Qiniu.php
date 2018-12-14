<?php

namespace App\facades;

use League\Flysystem\Filesystem;

/**
 * Class Qiniu
 *
 * @method static string bucket()
 * @method static string domain()
 * @method static Filesystem prepare($bucket = null)
 * @package App\facades
 */
class Qiniu extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\storage\Qiniu::create();
    }
}
