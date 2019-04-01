<?php

namespace App\components\chaos;

use App\components\http\Request;
use App\components\http\Response;
use App\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $chaosExpId = $request->header('x-chaos-exp-id');
        if (!is_null($chaosExpId)) {
            $faultData = $this->fetchFault($chaosExpId);
            if (!is_null($faultData)) {
                if (($faultResponse = $this->emulateFault($faultData)) instanceof Response) {
                    return $faultResponse;
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
            default:
                return null;
        }
    }
}
