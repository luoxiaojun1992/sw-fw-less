<?php

namespace SwFwLess\facades;

use League\Flysystem\Filesystem;
use SwFwLess\components\storage\Storage;

/**
 * Class Qiniu
 *
 * @method static Filesystem prepare($bucket = null)
 * @package SwFwLess\facades
 */
class Qiniu extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Storage::qiniu();
    }
}
