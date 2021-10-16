<?php

namespace SwFwLess\components\virtualization;

use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\virtualization\resource\CGroup;
use SwFwLess\components\virtualization\resource\Memory;

class Provider implements WorkerProviderContract
{
    public static function bootWorker()
    {
        if (CGroup::support()) {
            //todo
            $cGroupName = '';
            $pid = 0;
            $memoryLimit = 0;
            if (Memory::subCGroupExists($cGroupName)) {
                Memory::addCGroupProcess($cGroupName, $pid);
            } else {
                Memory::createCGroup($cGroupName, $pid);
            }
            Memory::limit($cGroupName, $memoryLimit);
        }
    }

    public static function shutdownWorker()
    {
        if (CGroup::support()) {
            //todo
            Memory::delCGroup('');
        }
    }
}
