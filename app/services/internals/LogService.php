<?php

namespace App\services\internals;

use App\components\http\Response;
use App\facades\Log;
use App\services\BaseService;

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
