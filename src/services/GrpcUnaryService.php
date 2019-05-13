<?php

namespace SwFwLess\services;

use SwFwLess\components\grpc\Serializer;
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
                    'grpc-message' => urlencode(''),
//                'grpc-message' => urlencode(Status::msg(Status::INVALID_ARGUMENT)),
                ]);
            }

            $options = Serializer::unpackHeader($body);
            if ($options['flag']) {
                throw new HttpException('', 404);
            }
            if ($options['length'] != (strlen($body) - 5)) {
                return Response::output('', 400, [], [
                    'grpc-status' => Status::INVALID_ARGUMENT,
                    'grpc-message' => urlencode(''),
//                'grpc-message' => urlencode(Status::msg(Status::INVALID_ARGUMENT)),
                ]);
            }
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
                    Serializer::unpack($protoRequest, $body, $isGrpc, (!$isGrpc) || $this->getRequest()->isGrpcJson());
                    $parameters[$handlerParameter->getName()] = $protoRequest;
                }
            }
        }
        $this->setParameters($parameters);

        return Response::grpc(parent::call(), 200, [], [], $isGrpcJson, $isGrpc);
    }
}
