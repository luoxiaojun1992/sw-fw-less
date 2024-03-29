<?php

namespace SwFwLess\components\storage\file;

use SwFwLess\components\Config;
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

    public static function clearInstance()
    {
        static::$instance = null;
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
        return ($this->config['storage_path']) ?? ($this->path('runtime/storage/'));
    }

    /**
     * @param int $writeFlags
     * @param int $linkHandling
     * @param array $permissions
     * @param null $root
     * @return Filesystem
     */
    public function prepare(
        $writeFlags = LOCK_EX,
        $linkHandling = Local::DISALLOW_LINKS,
        $permissions = [],
        $root = null
    )
    {
        $local = new Local($root ?: $this->storagePath(), $writeFlags, $linkHandling, $permissions);
        return new Filesystem($local);
    }
}
