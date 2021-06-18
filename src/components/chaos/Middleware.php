<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\http\Client;
use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $faultResponse = null;
        $chaosSwitch = \SwFwLess\components\functions\config('chaos.switch', false);
        if ($chaosSwitch) {
            $chaosExpId = $request->header('x-chaos-exp-id');
            if (!is_null($chaosExpId)) {
                $faultData = $this->fetchFault($chaosExpId);
                if (!is_null($faultData)) {
                    foreach ($faultData as $fault) {
                        if (($faultResponse = $this->emulateFault($fault)) instanceof Response) {
                            if ($fault['eager_response']) {
                                return $faultResponse;
                            }
                        }
                    }
                }
            }
        }

        $response = $this->next();
        if (!is_null($faultResponse)) {
            return $faultResponse;
        } else {
            return $response;
        }
    }

    private function fetchFault($chaosExpId)
    {
        $fault = FaultStore::get($chaosExpId);
        if ($fault) {
            $faultData = json_decode($fault, true);
            if (!json_last_error()) {
                return $faultData;
            }
        }

        return null;
    }

    private function emulateFault($faultData)
    {
        switch ($faultData['type']) {
            case 'exception':
                $exceptionClass = $faultData['class'];
                throw new $exceptionClass(
                    $faultData['msg'],
                    $faultData['code']
                );
            case 'response':
                return Response::output(
                    $faultData['content'],
                    $faultData['status'],
                    $faultData['headers']
                );
            case 'latency':
                usleep($faultData['duration']);
                return null;
            case 'memory_usage':
                $memoryCarrier = str_repeat('1', $faultData['memory_usage']);
                return null;
            case 'http_request':
                $httpUrl = $faultData['http_url'];
                $httpMethod = $faultData['http_method'];
                $httpHeaders = $faultData['http_headers'];
                $requestBody = $faultData['request_body'];
                $bodyType = $faultData['request_body_type'];
                switch ($httpMethod) {
                    case 'GET':
                        Client::get($httpUrl, null, $httpHeaders);
                        break;
                    case 'POST':
                        Client::post(
                            $httpUrl, null, $httpHeaders, $requestBody, $bodyType
                        );
                        break;
                    case 'PUT':
                        Client::put(
                            $httpUrl, null, $httpHeaders, $requestBody, $bodyType
                        );
                        break;
                    case 'DELETE':
                        Client::delete(
                            $httpUrl, null, $httpHeaders, $requestBody, $bodyType
                        );
                        break;
                }
                return null;
            default:
                return null;
        }
    }
}
