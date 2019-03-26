<?php

namespace App\components\storage;

use App\components\Config;

class Storage
{
    public static function init()
    {
        $storageConfig = Config::get('storage');
        foreach ($storageConfig['types'] as $storageType) {
            $storageClass = str_replace('Storage', $storageType . '\\' . ucfirst($storageType), __CLASS__);
            if (class_exists($storageClass)) {
                call_user_func([$storageClass, 'create']);
            }
        }
    }
}
