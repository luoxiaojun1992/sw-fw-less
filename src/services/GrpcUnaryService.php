<?php

namespace SwFwLess\services;

use Google\Protobuf\Internal\Message;
use SwFwLess\components\grpc\Serializer;
use SwFwLess\components\grpc\Status;
use SwFwLess\components\http\Response;
use SwFwLess\exceptions\HttpException;
use SwFwLess\facades\ObjectPool;

abstract class GrpcUnaryService extends BaseService
{
    public function requestMessageClass($method)
    {
        return null;
    }

    public function requestMessageName($method)
    {
        return null;
    }

    public function call()
    {
        try {
            //Verify protocol
            $body = $this->getRequest()->body();

            $isGrpc = $this->getRequest()->isGrpc();
            if ($isGrpc) {
                if (strlen($body) < 5) {
                    return Response::output('', 400, [], [
                        'grpc-status' => Status::INVALID_ARGUMENT,
                        'grpc-message' => urlencode(Status::msg(Status::INVALID_ARGUMENT)),
                    ]);
                }

                $options = Serializer::unpackHeader($body);
                if ($options['flag']) {
                    throw new HttpException('', 404);
                }
                if ($options['length'] != (strlen($body) - 5)) {
                    return Response::output('', 400, [], [
                        'grpc-status' => Status::INVALID_ARGUMENT,
                        'grpc-message' => urlencode(Status::msg(Status::INVALID_ARGUMENT)),
                    ]);
                }
            } else {
                if (!$this->getRequest()->isJson()) {
                    return Response::output('Unsupported conent type', 400);
                }
            }

            $isGrpcJson = $this->getRequest()->isGrpcJson();

            $parameters = $this->getParameters();

            $handler = $this->getHandler();

            $requestMessageClass = $this->requestMessageClass($handler);

            if (is_null($requestMessageClass)) {
                $reflectionHandler = new \ReflectionMethod($this, $handler);
                $handlerParameters = $reflectionHandler->getParameters();
                foreach ($handlerParameters as $handlerParameter) {
                    if ($declaringClass = $handlerParameter->getClass()) {
                        if ($declaringClass->isSubclassOf(\Google\Protobuf\Internal\Message::class)) {
                            /** @var Message $protoRequest */
                            $protoRequest = $declaringClass->newInstance();
                            Serializer::unpack($protoRequest, $body, $isGrpc, (!$isGrpc) || $isGrpcJson);
                            $parameters[$handlerParameter->getName()] = $protoRequest;
                        }
                    }
                }
            } else {
                $protoRequest = new $requestMessageClass;
                Serializer::unpack($protoRequest, $body, $isGrpc, (!$isGrpc) || $isGrpcJson);
                $parameters[$this->requestMessageName($handler) ?? 'request'] = $protoRequest;
            }

            $this->setParameters($parameters);

            return Response::grpc(parent::call(), 200, [], [], $isGrpcJson, $isGrpc);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            ObjectPool::release($this);
        }
    }
}
