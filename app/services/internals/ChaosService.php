<?php

namespace App\services\internals;

use App\components\chaos\FaultStore;
use App\components\http\Response;
use App\services\BaseService;

class ChaosService extends BaseService
{
    public function injectFault($id)
    {
        FaultStore::set($id, $this->getRequest()->body());
        return Response::json(['code' => 0, 'msg' => 'ok', 'data' => []]);
    }

    public function fetchFault($id)
    {
        return Response::json(['code' => 0, 'msg' => 'ok', 'data' => json_decode(FaultStore::get($id), true)]);
    }
}
