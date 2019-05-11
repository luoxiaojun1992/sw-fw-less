<?php

namespace SwFwLess\services;

use SwFwLess\components\http\Response;

class GrpcService extends BaseService
{
    public function call()
    {
        $parameters = $this->getParameters();
        $reflectionHandler = new \ReflectionMethod($this, $this->getHandler());
        $handlerParameters = $reflectionHandler->getParameters();
        foreach ($handlerParameters as $handlerParameter) {
            if ($declaringClass = $handlerParameter->getClass()) {
                if ($declaringClass->isSubclassOf(\Google\Protobuf\Internal\Message::class)) {
                    $protoRequest = $declaringClass->newInstance();
                    $protoRequest->mergeFromString(substr($this->getRequest()->body(), 5));
                    $parameters[$handlerParameter->getName()] = $protoRequest;
                }
            }
        }
        $this->setParameters($parameters);

        $response = parent::call();

        if ($response instanceof \Google\Protobuf\Internal\Message) {
            return Response::grpc($response);
        }

        return $response;
    }
}
