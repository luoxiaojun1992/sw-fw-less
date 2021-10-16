<?php

namespace SwFwLess\components\virtualization;

use SwFwLess\components\provider\AppProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\virtualization\resource\CGroup;
use SwFwLess\components\virtualization\resource\Memory;

class Provider implements AppProviderContract, WorkerProviderContract
{
    public static function bootWorker()
    {
        if (CGroup::support()) {
            //todo
            $cGroupName = '';
            $pid = 0;
            $memoryLimit = 0;
            Memory::addCGroupProcess($cGroupName, $pid);
            Memory::limit($cGroupName, $memoryLimit);
        }
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function bootApp()
    {
        if (CGroup::support()) {
            $cGroupName = '';
            if (Memory::subCGroupExists($cGroupName)) {
                Memory::delCGroup($cGroupName);
            } else {
                Memory::createCGroup($cGroupName);
            }
        }
    }

    public static function shutdownApp()
    {
        if (CGroup::support()) {
            $cGroupName = '';
            if (Memory::subCGroupExists($cGroupName)) {
                Memory::delCGroup($cGroupName);
            }
        }
    }
}
