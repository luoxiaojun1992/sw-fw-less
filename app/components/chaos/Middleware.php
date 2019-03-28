<?php

namespace App\components\chaos;

use App\components\http\Request;
use App\components\http\Response;
use App\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        if ($chaosId = $request->header('x-chaos-exp-id')) {
            //Fetch fault by chaos id
            $fault = FaultStore::get($chaosId);
            if ($fault) {
                $faultData = json_decode($fault, true);
                if (!json_last_error()) {
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
                    }
                }
            }
        }

        return $this->next();
    }
}
