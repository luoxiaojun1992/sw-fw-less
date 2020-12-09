<?php

use SwFwLess\components\http\Response;

class TestService extends \SwFwLess\services\BaseService
{
    public function ping()
    {
        return Response::output('pong');
    }

    public function call()
    {
        list($handler, $parameters) = $this->getHandlerAndParameters();
        $response = call_user_func_array([$this, $handler], $parameters);

        if (is_array($response)) {
            return Response::json($response);
        }

        return $response;
    }
}
