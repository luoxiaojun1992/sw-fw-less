<?php

namespace App\components\storage;

use App\components\Config;

class Storage
{
    public static function init()
    {
        $storageConfig = Config::get('storage');
        if ($storageConfig['switch']) {
            $storageTypes = $storageConfig['types'];
            foreach ($storageTypes as $storageType) {
                if (class_exists(ucfirst($storageType))) {
                    call_user_func([$storageType, 'create']);
                }
            }
        }
    }
}
