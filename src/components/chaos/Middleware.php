<?php

namespace SwFwLess\components\chaos;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $chaosExpId = $request->header('x-chaos-exp-id');
        if (!is_null($chaosExpId)) {
            $faultData = $this->fetchFault($chaosExpId);
            if (!is_null($faultData)) {
                foreach ($faultData as $fault) {
                    if (($faultResponse = $this->emulateFault($fault)) instanceof Response) {
                        return $faultResponse;
                    }
                }
            }
        }

        return $this->next();
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
            case 'delay':
                usleep($faultData['duration']);
                return null;
            case 'memory_usage':
                $memoryCarrier = str_repeat('1', $faultData['memory_usage']);
                return null;
            default:
                return null;
        }
    }
}
