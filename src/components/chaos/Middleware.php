<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\http\Client;
use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\swoole\Server;
use SwFwLess\components\utils\data\structure\Arr;
use SwFwLess\facades\Math;
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
            case 'reload':
                Server::getInstance()->reload();
                return null;
            case 'kill':
                exit(1);
            case 'callable':
                call_user_func_array($faultData['callable'], $faultData['callable_params']);
                return null;
            case 'cpu_load':
                mt_srand(time());
                $cpuCalType = $faultData['cal_type'] ?? 'add';
                $cpuCalTimes = $faultData['cal_times'] ?? 10000;
                for ($i = 0; $i < $cpuCalTimes; ++$i) {
                    if (Arr::safeInArray($cpuCalType, ['add', 'sub', 'mul', 'div', 'cmp'])) {
                        $vector1 = Math::createCFloatNumbers(4);
                        $vector2 = Math::createCFloatNumbers(4);
                        for ($vectorI = 0; $vectorI < 4; ++$vectorI) {
                            $vector1[$vectorI] = floatval(mt_rand(100000, 999999));
                            $vector2[$vectorI] = floatval(mt_rand(100000, 999999));
                        }
                        if ($cpuCalType === 'add') {
                            Math::vectorAdd($vector1, $vector2, 4);
                        } elseif ($cpuCalType === 'sub') {
                            Math::vectorSub($vector1, $vector2, 4);
                        } elseif ($cpuCalType === 'mul') {
                            Math::vectorMul($vector1, $vector2, 4);
                        } elseif ($cpuCalType === 'div') {
                            Math::vectorDiv($vector1, $vector2, 4);
                        } elseif ($cpuCalType === 'cmp') {
                            Math::vectorCmp($vector1, $vector2, 4);
                        }
                    } elseif (Arr::safeInArray($cpuCalType, ['sqrt', 'rcp', 'ceil', 'floor', 'round'])) {
                        $vector1 = Math::createCFloatNumbers(4);
                        for ($vectorI = 0; $vectorI < 4; ++$vectorI) {
                            $vector1[$vectorI] = floatval(mt_rand(100000, 999999));
                        }
                        if ($cpuCalType === 'sqrt') {
                            Math::vectorSqrt($vector1, 4);
                        } elseif ($cpuCalType === 'rcp') {
                            Math::vectorRcp($vector1, 4);
                        } elseif ($cpuCalType === 'ceil') {
                            Math::vectorCeil($vector1, 4);
                        } elseif ($cpuCalType === 'floor') {
                            Math::vectorFloor($vector1, 4);
                        } elseif ($cpuCalType === 'round') {
                            Math::vectorRound($vector1, 4);
                        }
                    } elseif (Arr::safeInArray($cpuCalType, ['abs'])) {
                        $vector1 = Math::createCIntNumbers(4);
                        for ($vectorI = 0; $vectorI < 4; ++$vectorI) {
                            $vector1[$vectorI] = mt_rand(100000, 999999);
                        }
                        if ($cpuCalType === 'abs') {
                            Math::vectorAbs($vector1, 4);
                        }
                    }
                }
                return null;
            default:
                return null;
        }
    }
}
