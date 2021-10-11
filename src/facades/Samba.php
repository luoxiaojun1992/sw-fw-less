<?php

namespace SwFwLess\facades;

use League\Flysystem\Filesystem;
use SwFwLess\components\storage\Storage;

/**
 * Class Samba
 *
 * @method static Filesystem prepare($workgroup, $shareName)
 * @package SwFwLess\facades
 */
class Samba extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Storage::samba();
    }
}
