<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use Symfony\Component\Console\Output\ConsoleOutput;

class Trace extends AbstractMiddleware
{
    protected function requestInfo(Request $request, Response $response, $requestDuration)
    {
        $output = new ConsoleOutput();
        $requestMethod = $request->method();
        $uri = $request->uri();
        $httpCode = $response->getStatus();
        if ($requestDuration >= 1) {
            $formattedReqDuration = round($requestDuration, 2);
            $requestDurationText = $formattedReqDuration . ' sec';
        } elseif ($requestDuration >= 0.001) {
            $formattedReqDuration = round($requestDuration * 1000, 2);
            $requestDurationText = $formattedReqDuration . ' ms';
        } else {
            $formattedReqDuration = round($requestDuration * 1000000, 2);
            $requestDurationText = $formattedReqDuration . ' us';
        }
        $output->writeln("<info>{$httpCode}    {$requestMethod} {$uri}    {$requestDurationText}</info>");
    }

    public function handle(Request $request)
    {
        $requestTs = microtime(true);
        $response = $this->next();
        $requestDuration = microtime(true) - $requestTs;
        $this->requestInfo($request, $response, $requestDuration);
        return $response;
    }
}
