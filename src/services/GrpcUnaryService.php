<?php

namespace SwFwLess\services;

use SwFwLess\components\grpc\Status;
use SwFwLess\components\http\Response;
use SwFwLess\exceptions\HttpException;

class GrpcUnaryService extends BaseService
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

                        if (strlen($body) < 5) {
                            return Response::output('', 400, [], [
                                'grpc-status' => Status::INVALID_ARGUMENT,
                                'grpc-message' => Status::msg(Status::INVALID_ARGUMENT),
                            ]);
                        }

                        $options = unpack('CflagNlength', substr($body, 0, 5));
                        if ($options['flag']) {
                            throw new HttpException('Grpc message flag error', 404);
                        }
                        if ($options['length'] != (strlen($body) - 5)) {
                            return Response::output('', 400, [], [
                                'grpc-status' => Status::INVALID_ARGUMENT,
                                'grpc-message' => Status::msg(Status::INVALID_ARGUMENT),
                            ]);
                        }

                        $protoRequest->mergeFromString(substr($body, 5));
                    } else {
                        $protoRequest->mergeFromJsonString($this->getRequest()->body());
                    }
                    $parameters[$handlerParameter->getName()] = $protoRequest;
                }
            }
        }
        $this->setParameters($parameters);

        return Response::grpc(parent::call(), 200, [], [], !$this->getRequest()->isGrpc());
    }
}
