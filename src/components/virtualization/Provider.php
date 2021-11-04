<?php

namespace SwFwLess\components\virtualization;

use SwFwLess\components\Config;
use SwFwLess\components\provider\AppProviderContract;
use SwFwLess\components\provider\WorkerProviderContract;
use SwFwLess\components\swoole\Server;
use SwFwLess\components\virtualization\resource\CGroup;
use SwFwLess\components\virtualization\resource\Memory;

class Provider implements AppProviderContract, WorkerProviderContract
{
    protected static function getVirtualizationConfig()
    {
        return Config::get('virtualization');
    }

    protected static function getVirtualizationSwitch($config = [])
    {
        return $config['switch'] ?? false;
    }

    protected static function getVirtualizationName($config = [])
    {
        return $config['name'] ?? 'sw-fw-less';
    }

    public static function bootWorker()
    {
        if (CGroup::support()) {
            $virtualizationConfig = static::getVirtualizationConfig();
            if (static::getVirtualizationSwitch($virtualizationConfig)) {
                $cGroupName = static::getVirtualizationName($virtualizationConfig);
                $pid = Server::getInstance()->worker_pid;
                $memoryLimit = $virtualizationConfig['memory_limit'] ?? 10240000;
                Memory::addCGroupProcess($cGroupName, $pid);
                Memory::limit($cGroupName, $memoryLimit);
            }
        }
    }

    public static function shutdownWorker()
    {
        //
    }

    public static function bootApp()
    {
        if (CGroup::support()) {
            $virtualizationConfig = static::getVirtualizationConfig();
            if (static::getVirtualizationSwitch($virtualizationConfig)) {
                $cGroupName = static::getVirtualizationName($virtualizationConfig);
                if (Memory::subCGroupExists($cGroupName)) {
                    Memory::delCGroup($cGroupName);
                }
                Memory::createCGroup($cGroupName);
            }
        }
    }

    public static function shutdownApp()
    {
        if (CGroup::support()) {
            $virtualizationConfig = static::getVirtualizationConfig();
            if (static::getVirtualizationSwitch($virtualizationConfig)) {
                $cGroupName = static::getVirtualizationName($virtualizationConfig);
                if (Memory::subCGroupExists($cGroupName)) {
                    Memory::delCGroup($cGroupName);
                }
            }
        }
    }
}
