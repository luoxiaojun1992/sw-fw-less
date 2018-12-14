<?php

namespace App\components\storage;

use App\components\Config;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class File
{
    private static $instance;

    private $config;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('storage');
    }

    /**
     * @return File|null
     */
    public static function create()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $storageConfig = Config::get('storage');
        if ($storageConfig['switch']) {
            if (in_array('file', $storageConfig['types'])) {
                return self::$instance = new self();
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function basePath()
    {
        return $this->config['base_path'];
    }

    /**
     * @param $relativePath
     * @return string
     */
    public function path($relativePath)
    {
        return $this->basePath() . $relativePath;
    }

    /**
     * @return string
     */
    public function appPath()
    {
        return $this->path('app/');
    }

    /**
     * @return string
     */
    public function storagePath()
    {
        return isset($this->config['storage_path']) ?
            $this->config['storage_path'] :
            $this->path('runtime/storage/');
    }

    /**
     * @param int $writeFlags
     * @param int $linkHandling
     * @param array $permissions
     * @return Filesystem
     */
    public function prepare($writeFlags = LOCK_EX, $linkHandling = Local::DISALLOW_LINKS, $permissions = [])
    {
        $local = new Local($this->storagePath(), $writeFlags, $linkHandling, $permissions);
        return new Filesystem($local);
    }
}
