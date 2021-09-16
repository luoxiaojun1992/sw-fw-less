<?php

namespace SwFwLess\facades;

/**
 * Class Math
 *
 * @method static writeFile($filepath, $content)
 *
 * @package SwFwLess\facades
 */
class MemoryMap extends AbstractFacade
{
    /**
     * @return \SwFwLess\components\traits\Singleton|\SwFwLess\components\storage\file\mmap\MemoryMap|null
     * @throws \Exception
     */
    protected static function getAccessor()
    {
        return \SwFwLess\components\storage\file\mmap\MemoryMap::create([]);
    }
}
