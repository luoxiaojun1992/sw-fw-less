<?php

namespace SwFwLess\services;

use SwFwLess\components\grpc\Status;
use SwFwLess\components\http\Response;
use SwFwLess\exceptions\HttpException;

class GrpcUnaryService extends BaseService
{
    public function call()
    {
        //Verify protocol
        $body = $this->getRequest()->body();

        $isGrpc = $this->getRequest()->isGrpc();
        if ($isGrpc) {
            if (strlen($body) < 5) {
                return Response::output('', 400, [], [
                    'grpc-status' => Status::INVALID_ARGUMENT,
                    'grpc-message' => '',
//                'grpc-message' => Status::msg(Status::INVALID_ARGUMENT),
                ]);
            }

            $options = unpack('Cflag/Nlength', substr($body, 0, 5));
            if ($options['flag']) {
                throw new HttpException('', 404);
            }
            if ($options['length'] != (strlen($body) - 5)) {
                return Response::output('', 400, [], [
                    'grpc-status' => Status::INVALID_ARGUMENT,
                    'grpc-message' => '',
//                'grpc-message' => Status::msg(Status::INVALID_ARGUMENT),
                ]);
            }

            $body = substr($body, 5);
        } else {
            if (!$this->getRequest()->isJson()) {
                return Response::output('Unsupported conent type', 400);
            }
        }

        $isGrpcJson = $this->getRequest()->isGrpcJson();

        $parameters = $this->getParameters();
        $reflectionHandler = new \ReflectionMethod($this, $this->getHandler());
        $handlerParameters = $reflectionHandler->getParameters();
        foreach ($handlerParameters as $handlerParameter) {
            if ($declaringClass = $handlerParameter->getClass()) {
                if ($declaringClass->isSubclassOf(\Google\Protobuf\Internal\Message::class)) {
                    $protoRequest = $declaringClass->newInstance();

                    if ($isGrpc) {
                        if ($this->getRequest()->isGrpcJson()) {
                            $protoRequest->mergeFromJsonString($body);
                        } else {
                            $protoRequest->mergeFromString($body);
                        }
                    } else {
                        $protoRequest->mergeFromJsonString($body);
                    }

                    $parameters[$handlerParameter->getName()] = $protoRequest;
                }
            }
        }
        $this->setParameters($parameters);

        return Response::grpc(parent::call(), 200, [], [], $isGrpcJson, $isGrpc);
    }
}
