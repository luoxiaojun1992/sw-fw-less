<?php

namespace SwFwLess\services\internals;

use SwFwLess\components\http\Response;
use SwFwLess\services\BaseService;

class DatetimeService extends BaseService
{
    public function timestamp()
    {
        return Response::json([
            'code' => 0, 'msg' => 'ok',
            'data' => ['timestamp' => microtime(true)]
        ]);
    }
}
