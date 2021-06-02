<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Code;
use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use Symfony\Component\Console\Output\ConsoleOutput;

class Trace extends AbstractMiddleware
{
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

        if ($requestDuration >= 1) {
            $formattedReqDuration = round($requestDuration, 2);
            $requestDurationText = ((string)$formattedReqDuration) . ' sec';
        } elseif ($requestDuration >= 0.001) {
            $formattedReqDuration = round($requestDuration * 1000, 2);
            $requestDurationText = ((string)$formattedReqDuration) . ' ms';
        } else {
            $formattedReqDuration = round($requestDuration * 1000000, 2);
            $requestDurationText = ((string)$formattedReqDuration) . ' us';
        }

        if ($memoryUsage >= 1000000000) { //GB
            $formattedMemUsage = round($memoryUsage / 1000000000, 2);
            $memUsageText = ((string)$formattedMemUsage) . ' GB';
        } elseif ($memoryUsage >= 1000000) { //MB
            $formattedMemUsage = round($memoryUsage / 1000000, 2);
            $memUsageText = ((string)$formattedMemUsage) . ' MB';
        } elseif ($memoryUsage >= 1000) { //KB
            $formattedMemUsage = round($memoryUsage / 1000, 2);
            $memUsageText = ((string)$formattedMemUsage) . ' KB';
        } else {
            $memUsageText = ((string)$memoryUsage) . ' Bytes';
        }

        $output->writeln(
            "<{$traceLevel}>{$httpCode}</{$traceLevel}>" .
            "    " .
            "<info>{$requestMethod} {$uri}" .
            "    " .
            "{$memUsageText}" .
            "    " .
            "{$requestDurationText}</info>"
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
