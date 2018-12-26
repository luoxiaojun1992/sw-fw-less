<?php

namespace App\facades;

use League\Flysystem\Filesystem;

/**
 * Class Alioss
 *
 * @method static Filesystem prepare($bucket = null)
 * @package App\facades
 */
class Alioss extends AbstractFacade
{
    protected static function getAccessor()
    {
        return \App\components\storage\alioss\Alioss::create();
    }
}
