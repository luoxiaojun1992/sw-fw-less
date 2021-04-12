<?php

namespace SwFwLess\facades;

use League\Flysystem\Filesystem;
use SwFwLess\components\storage\Storage;

/**
 * Class Alioss
 *
 * @method static Filesystem prepare($bucket = null)
 * @package SwFwLess\facades
 */
class Alioss extends AbstractFacade
{
    protected static function getAccessor()
    {
        return Storage::alioss();
    }
}
