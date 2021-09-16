<?php

namespace SwFwLess\components\storage\file\mmap;

use League\Flysystem\Adapter\Local;
use SwFwLess\components\utils\OS;
use SwFwLess\components\utils\Runtime;
use SwFwLess\facades\File;

class MemoryMap
{
    protected $config = [];
    protected $ffiPath;
    protected $udf;

    /**
     * @param array $config
     * @return MemoryMap|static
     */
    public static function create($config = [])
    {
        return new self($config);
    }

    public function __construct($config = [])
    {
        $this->config = $config;

        if (Runtime::supportFFI()) {
            $osType = OS::type();
            if ($osType === OS::OS_LINUX) {
                $this->ffiPath = __DIR__ . '/ffi/c/linux/libcmmap.so';
            } elseif ($osType === OS::OS_DARWIN) {
                $this->ffiPath = __DIR__ . '/ffi/c/darwin/libcmmap.so';
            }
        }

        if ($this->ffiPath) {
            $this->udf = $this->createUdf($this->ffiPath);
        }
    }

    protected function createUdf($ffiPath)
    {
        return \FFI::cdef(
            "int WriteFile(const char *pathname, const char *content);",
            $ffiPath
        );
    }

    protected function nativeWriteFile($filepath, $content)
    {
        return File::prepare(
            LOCK_EX,
            Local::DISALLOW_LINKS,
            [],
            dirname($filepath)
        )->write(basename($filepath), $content);
    }

    public function writeFile($filepath, $content)
    {
        if (!Runtime::supportFFI()) {
            return $this->nativeWriteFile($filepath, $content);
        }

        if (!$this->ffiPath) {
            return $this->nativeWriteFile($filepath, $content);
        }

        return !((bool)($this->udf->WriteFile($filepath, $content)));
    }
}
