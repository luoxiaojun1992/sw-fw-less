<?php

namespace SwFwLess\services;

use SwFwLess\components\http\Response;
use SwFwLess\exceptions\ValidationException;

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
                    if ($this->getRequest()->isGrpc()) {
                        $body = $this->getRequest()->body();
                        $this->verifyBody($body);
                        $protoRequest->mergeFromString(substr($body, 5));
                    } else {
                        $protoRequest->mergeFromJsonString($this->getRequest()->body());
                    }
                    $parameters[$handlerParameter->getName()] = $protoRequest;
                }
            }
        }
        $this->setParameters($parameters);

        $response = parent::call();

        if ($response instanceof \Google\Protobuf\Internal\Message) {
            return Response::grpc($response, 200, [], !$this->getRequest()->isGrpc());
        }

        return $response;
    }

    /**
     * @param $body
     */
    private function verifyBody($body)
    {
        //todo grpc status
        if (strlen($body) < 5) {
            throw new ValidationException(['Grpc body length error']);
        }
        $options = unpack('CflagNlength', substr($body, 0, 5));
        if ($options['flag']) {
            throw new ValidationException(['Grpc message flag error']);
        }
        if ($options['length'] != (strlen($body) - 5)) {
            throw new ValidationException(['Grpc message length error']);
        }
    }
}
