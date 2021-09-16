<?php

namespace SwFwLessTest\components\storage\file\mmap;

use PHPUnit\Framework\TestCase;
use SwFwLess\facades\MemoryMap;

class MemoryMapTest extends TestCase
{
    public function testWriteFile()
    {
        $filePath = __DIR__ . '/../../../../output/test_mmap.txt';
        touch($filePath);
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
        }

        $this->assertEquals($testContent, $fileContent);

        unlink($filePath);
    }

    public function testAppendFile()
    {
        $filePath = __DIR__ . '/../../../../output/test_mmap_append.txt';
        touch($filePath);
        file_put_contents($filePath, 'test content');
        $this->assertTrue(MemoryMap::appendFile(
            $filePath,
            ' test content'
        ));

        $testContent = 'test content test content';

        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== $testContent) {
            if (time() - $startTime > 5) {
                break;
            }
        }

        $this->assertEquals(
            $testContent,
            $fileContent
        );

        unlink($filePath);
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
}
