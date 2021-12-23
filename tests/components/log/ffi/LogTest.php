<?php

namespace SwFwLessTest\components\log\ffi;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\log\ffi\Log;
use SwFwLess\facades\CLog;

class LogTest extends TestCase
{
    protected function logPath()
    {
        return __DIR__ . '/../../../output/test_clog.log';
    }

    protected function log($content, $level = Log::LEVEL_TRACE, $file = '', $line = 0)
    {
        $logPath = $this->logPath();
        return CLog::log($logPath, $content, $level, $file, $line);
    }

    public function testLog()
    {
        $logPath = $this->logPath();
        if (file_exists($logPath)) {
            unlink($logPath);
        }

        foreach ([
            Log::LEVEL_TRACE,
            Log::LEVEL_DEBUG,
            Log::LEVEL_INFO,
            Log::LEVEL_WARN,
            Log::LEVEL_ERROR,
            Log::LEVEL_FATAL] as $logLevel) {
            $this->assertTrue($this->log('test ' . ((string)$logLevel), $logLevel));
        }

        $expectedLogParts = [
            ['TRACE', ':0:', 'test', '0'],
            ['DEBUG', ':0:', 'test', '1'],
            ['INFO', ':0:', 'test', '2'],
            ['WARN', ':0:', 'test', '3'],
            ['ERROR', ':0:', 'test', '4'],
            ['FATAL', ':0:', 'test', '5'],
        ];

        $result = false;
        $startTime = time();
        while (true) {
            if (time() - $startTime > 5) {
                break;
            }

            foreach (file($logPath) as $i => $line) {
                if ($i >= 6) {
                    break;
                }
                $log = rtrim($line, PHP_EOL);
                $logParts = explode(' ', $log);
                $actualLogParts = array_slice($logParts, 2);
                $actualLogParts = array_values(array_filter($actualLogParts, function ($part) {
                    return $part !== '';
                }));
                if ($actualLogParts !== $expectedLogParts[$i]) {
                    $result = false;
                    break;
                } else {
                    $result = true;
                }
            }

            if ($result) {
                break;
            }

            sleep(1);
        }

        $this->assertTrue($result);

        unlink($logPath);
    }
}
