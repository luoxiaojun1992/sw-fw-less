<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Code;
use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\swoole\Server;
use Swoole\Coroutine;
use Symfony\Component\Console\Output\ConsoleOutput;

class Trace extends AbstractMiddleware
{
    protected function formatDuration($duration)
    {
        if ($duration >= 1) {
            $formattedDuration = round($duration, 2);
            $durationUnit = 'sec';
        } elseif ($duration >= 0.001) {
            $formattedDuration = round($duration * 1000, 2);
            $durationUnit = 'ms';
        } else {
            $formattedDuration = round($duration * 1000000, 2);
            $durationUnit = 'us';
        }
        return [$formattedDuration, $durationUnit];
    }

    protected function formatMemUsage($memUsage)
    {
        if ($memUsage >= 1000000000) { //GB
            $formattedMemUsage = round($memUsage / 1000000000, 2);
            $memUsageUnit = 'GB';
        } elseif ($memUsage >= 1000000) { //MB
            $formattedMemUsage = round($memUsage / 1000000, 2);
            $memUsageUnit = 'MB';
        } elseif ($memUsage >= 1000) { //KB
            $formattedMemUsage = round($memUsage / 1000, 2);
            $memUsageUnit = 'KB';
        } else {
            $formattedMemUsage = $memUsage;
            $memUsageUnit = 'Bytes';
        }
        return [$formattedMemUsage, $memUsageUnit];
    }

    protected function requestInfo(
        Request $request, ?Response $response, $requestDuration, $memoryUsage
    )
    {
        $output = new ConsoleOutput();
        $requestMethod = $request->method();
        $uri = $request->uri();
        $httpCode = $response ? ($response->getStatus()) : (Code::STATUS_INTERNAL_SERVER_ERROR);
        if ($httpCode < 400) {
            $traceLevel = 'info';
        } elseif ($httpCode < 500) {
            $traceLevel = 'comment';
        } else {
            $traceLevel = 'error';
        }

        list($formattedReqDuration, $reqDurationUnit) = $this->formatDuration($requestDuration);
        $requestDurationText = ((string)$formattedReqDuration) . ' ' . $reqDurationUnit;

        list($formattedMemUsage, $memUsageUnit) = $this->formatMemUsage($memoryUsage);
        $memUsageText = ((string)$formattedMemUsage) . ' ' . $memUsageUnit;

        $swooleServer = Server::getInstance();

        $pid = $swooleServer->worker_pid;
        $coroutineId = Coroutine::getCid();

        $output->writeln(
            "<{$traceLevel}>{$httpCode}</{$traceLevel}>" .
            "    " .
            "<info>{$requestMethod} {$uri}" .
            "    " .
            "{$memUsageText}" .
            "    " .
            "{$requestDurationText}".
            "    " .
            "PID:{$pid}" .
            "    " .
            "CID:{$coroutineId}" .
            "</info>"
        );
    }

    public function handle(Request $request)
    {
        $beforeReqMem = memory_get_usage();
        $requestTs = microtime(true);
        $response = null;
        try {
            $response = $this->next();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $requestDuration = microtime(true) - $requestTs;
            $requestMemUsage = memory_get_usage() - $beforeReqMem;
            $this->requestInfo($request, $response, $requestDuration, $requestMemUsage);
        }
        return $response;
    }
}
