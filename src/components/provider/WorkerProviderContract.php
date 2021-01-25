<?php

namespace SwFwLess\components\provider;

interface WorkerProviderContract
{
    public static function bootWorker();
    public static function shutdownWorker();
}
