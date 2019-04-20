<?php

namespace SwFwLess\facades;

use League\Flysystem\Filesystem;

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
        return \SwFwLess\components\storage\qiniu\Qiniu::create();
    }
}
