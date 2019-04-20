<?php

namespace SwFwLess\services\internals;

use SwFwLess\components\chaos\FaultStore;
use SwFwLess\components\http\Response;
use SwFwLess\services\BaseService;

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
