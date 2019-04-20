<?php

namespace SwFwLess\services\internals;

use SwFwLess\components\http\Response;
use SwFwLess\facades\Log;
use SwFwLess\services\BaseService;

class LogService extends BaseService
{
    /**
     * @return Response
     */
    public function flush()
    {
        Log::flush();
        return Response::json(['code' => 0, 'msg' => 'ok']);
    }
}
