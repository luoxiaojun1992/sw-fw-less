<?php

namespace SwFwLess\components\volcano\http;

use SwFwLess\components\http\Client;
use SwFwLess\components\volcano\AbstractOperator;
use Swlib\Saber\Request;

class HttpRequest extends AbstractOperator
{
    /** @var Request[] */
    protected $requests = [];

    protected $cursor = 0;

    protected $swfRequest;

    protected $preRequest = false;

    protected $response = [];

    protected $requestExceptions = [];

    protected $requestTraceConfig = [];

    public function next()
    {
        while (isset($this->requests[$this->cursor])) {
            if (isset($this->requestExceptions[$this->cursor])) {
                throw $this->requestExceptions[$this->cursor];
            }
            if (isset($this->response[$this->cursor])) {
                yield $this->response[$this->cursor];
            } else {
                $request = $this->requests[$this->cursor];
                $requestTraceConfig = $this->requestTraceConfig[$this->cursor];
                $response = Client::sendRequest(
                    $request, $this->swfRequest, $requestTraceConfig['with_trace'] ?? false,
                    $requestTraceConfig['span_name'] ?? null,
                    $requestTraceConfig['inject_span_ctx'] ?? true,
                    $requestTraceConfig['flushing_trace'] ?? false
                );
                ++$this->cursor;
                yield $response;
            }
        }
    }

    public function setSwfRequest($swfRequest)
    {
        $this->swfRequest = $swfRequest;
        return $this;
    }

    public function addRequest(
        $url, $method, $headers = [], $body = null, $bodyType = Client::JSON_BODY, $bodyLength = null,
        $spanName = null, $injectSpanCtx = true, $flushingTrace = false, $withTrace = false
    )
    {
        $request = Client::makeRequest(
            $url, $method, $headers, $body, $bodyType, $bodyLength
        );
        $this->requests[] = $request;
        $this->requestTraceConfig[] = [
            'span_name' => $spanName,
            'inject_span_ctx' => $injectSpanCtx,
            'flushing_trace' => $flushingTrace,
            'with_trace' => $withTrace,
        ];
        return $this;
    }

    public function info()
    {
        $requests = [];
        foreach ($this->requests as $i => $request) {
            $body = $request->getBody();
            $body = clone $body;
            $bodyContents = $body->getContents();
            $bodyLength = strlen($bodyContents);
            $requests[] = [
                'url' => (string)($request->getUri()),
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(false, true),
                'body' => $bodyContents,
                'body_length' => $bodyLength,
                'trace_config' => $this->requestTraceConfig[$i],
            ];
        }

        return [
            'pre_request' => $this->preRequest,
            'request_count' => count($this->requests),
            'requests' => $requests,
        ];
    }

    public function preRequest()
    {
        list($this->response, $this->requestExceptions) = Client::sendMultiRequest(
            $this->requests,
            $this->swfRequest,
            $this->requestTraceConfig
        );
        return $this;
    }

    public static function create($info = [])
    {
        $httpRequest = parent::create();
        if ($info) {
            if (isset($info['pre_request'])) {
                $httpRequest->preRequest = $info['pre_request'];
            }
            if (isset($info['requests'])) {
                foreach ($info['requests'] as $i => $request) {
                    $httpRequest->addRequest(
                        $request['url'],
                        $request['method'],
                        $request['headers'] ?? [],
                        $request['body'] ?? null,
                        Client::STRING_BODY,
                        $request['body_length'] ?? null
                    );
                    $httpRequest->requestTraceConfig[$i] = $request['trace_config'];
                }
            }
        }
        return $httpRequest;
    }
}
