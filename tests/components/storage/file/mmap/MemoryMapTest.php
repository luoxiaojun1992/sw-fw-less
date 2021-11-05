<?php

namespace SwFwLessTest\components\storage\file\mmap;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\Config;
use SwFwLess\components\storage\file\File;
use SwFwLess\facades\MemoryMap;

class MemoryMapTest extends TestCase
{
    public function testWriteFile()
    {
        $filePath = __DIR__ . '/../../../../output/test_mmap.txt';
        $testContent = 'test content';
        $this->assertTrue(MemoryMap::writeFile(
            $filePath,
            $testContent
        ));

        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== $testContent) {
            if (time() - $startTime > 5) {
                break;
            }
            sleep(1);
        }

        $this->assertEquals($testContent, $fileContent);

        unlink($filePath);
    }

    public function testNativeWriteFile()
    {
        Config::initByArr([
            'storage' => [
                'switch' => 1,
                'types' => ['file'],
            ]
        ]);

        $filePath = __DIR__ . '/../../../../output/test_mmap.txt';
        $testContent = 'test content';
        $this->assertTrue(MemoryMap::writeFile(
            $filePath,
            $testContent,
            true
        ));

        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== $testContent) {
            if (time() - $startTime > 5) {
                break;
            }
            sleep(1);
        }

        $this->assertEquals($testContent, $fileContent);

        unlink($filePath);

        Config::clear();
        File::clearInstance();
    }

    public function testAppendFile()
    {
        $filePath = __DIR__ . '/../../../../output/test_mmap_append.txt';
        touch($filePath);
        $testContent = 'test content';
        file_put_contents($filePath, 'test content');
        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== 'test content') {
            if (time() - $startTime > 5) {
                break;
            }
            sleep(1);
        }
        $this->assertEquals(
            $testContent,
            $fileContent
        );

        $this->assertTrue(MemoryMap::appendFile(
            $filePath,
            ' ' . $testContent
        ));

        $doubleTestContent = ($testContent . ' ' . $testContent);

        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== $doubleTestContent) {
            if (time() - $startTime > 5) {
                break;
            }
            sleep(1);
        }

        $this->assertEquals(
            $doubleTestContent,
            $fileContent
        );

        unlink($filePath);
    }

    public function testNativeAppendFile()
    {
        Config::initByArr([
            'storage' => [
                'switch' => 1,
                'types' => ['file'],
            ]
        ]);

        $filePath = __DIR__ . '/../../../../output/test_mmap_append.txt';
        touch($filePath);
        $testContent = 'test content';
        file_put_contents($filePath, 'test content');
        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== 'test content') {
            if (time() - $startTime > 5) {
                break;
            }
            sleep(1);
        }
        $this->assertEquals(
            $testContent,
            $fileContent
        );

        $this->assertTrue(MemoryMap::appendFile(
            $filePath,
            ' ' . $testContent,
            true
        ));

        $doubleTestContent = ($testContent . ' ' . $testContent);

        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== $doubleTestContent) {
            if (time() - $startTime > 5) {
                break;
            }
            sleep(1);
        }

        $this->assertEquals(
            $doubleTestContent,
            $fileContent
        );

        unlink($filePath);

        Config::clear();
        File::clearInstance();
    }

    public function testReadFile()
    {
        $this->assertEquals(
            'test content',
            MemoryMap::readFile(
                __DIR__ . '/../../../../stubs/components/storage/file/mmap/test.txt'
            )
        );
    }

    public function testNativeReadFile()
    {
        Config::initByArr([
            'storage' => [
                'switch' => 1,
                'types' => ['file'],
            ]
        ]);

        $this->assertEquals(
            'test content',
            MemoryMap::readFile(
                __DIR__ . '/../../../../stubs/components/storage/file/mmap/test.txt',
                true
            )
        );

        Config::clear();
        File::clearInstance();
    }
}
