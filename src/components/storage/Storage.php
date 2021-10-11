<?php

namespace SwFwLess\components\storage;

use SwFwLess\components\Config;
use SwFwLess\components\storage\alioss\Alioss;
use SwFwLess\components\storage\file\File;
use SwFwLess\components\storage\qiniu\Qiniu;
use SwFwLess\components\storage\samba\Samba;

class Storage
{
    public static function init()
    {
        $storageConfig = Config::get('storage');
        if ($storageConfig['switch']) {
            $storageTypes = $storageConfig['types'];
            foreach ($storageTypes as $storageType) {
                $storageClass = str_replace('Storage', $storageType . '\\' . ucfirst($storageType), __CLASS__);
                if (class_exists($storageClass)) {
                    call_user_func([$storageClass, 'create']);
                }
            }
        }
    }

    public static function alioss()
    {
        return Alioss::create();
    }

    public static function file()
    {
        return File::create();
    }

    public static function qiniu()
    {
        return Qiniu::create();
    }

    public static function samba()
    {
        return Samba::create();
    }
}
