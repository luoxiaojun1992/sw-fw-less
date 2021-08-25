<?php

namespace SwFwLessTest\components\utils;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\Config;
use SwFwLess\components\storage\file\File;
use SwFwLess\components\utils\FilesystemUtil;

class FilesystemUtilTest extends TestCase
{
    public function testScanDir()
    {
        $files = FilesystemUtil::scanDir(__DIR__ . '/../../stubs/components/utils/filesystem_util/*');
        $fileNames = [];
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileNames[] = basename($file);
            }
        }
        $this->assertTrue(
            in_array('6905700.jpeg', $fileNames)
        );
    }

    public function testSize()
    {
        $this->assertEquals(
            7229,
            FilesystemUtil::size(
                __DIR__ . '/../../stubs/components/utils/filesystem_util/6905700.jpeg'
            )
        );
    }

    public function testMimeType()
    {
        Config::initByArr([
            'storage' => [
                'switch' => 1,
                'types' => ['file'],
            ]
        ]);
        $this->assertEquals(
            'image/jpeg',
            strtolower(
                FilesystemUtil::mimetype(
                    __DIR__ . '/../../stubs/components/utils/filesystem_util/6905700.jpeg'
                )
            )
        );
        Config::clear();
        File::clearInstance();
    }
}
