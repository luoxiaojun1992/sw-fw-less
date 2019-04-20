<?php

namespace SwFwLess\facades;

use League\Flysystem\Filesystem;

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
        return \SwFwLess\components\storage\alioss\Alioss::create();
    }
}
