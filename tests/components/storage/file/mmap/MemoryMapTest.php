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
        MemoryMap::writeFile(
            $filePath,
            $testContent
        );

        $startTime = time();
        while (($fileContent = file_get_contents($filePath)) !== $testContent) {
            if (time() - $startTime > 5) {
                break;
            }
        }

        $this->assertEquals($testContent, $fileContent);

        unlink($filePath);

    }
}
