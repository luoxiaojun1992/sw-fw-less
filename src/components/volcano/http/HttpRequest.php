<?php

namespace SwFwLess\components\volcano\http;

use SwFwLess\components\http\Client;
use SwFwLess\components\volcano\AbstractOperator;
use SwFwLess\components\zipkin\HttpClient;
use Swlib\Http\BufferStream;
use Swlib\Http\ContentType;
use Swlib\Saber\Request;
use Swlib\Http\Uri;
use Swlib\SaberGM;

class HttpRequest extends AbstractOperator
{
    /** @var Request[] */
    protected $requests = [];

    protected $cursor = 0;

    protected $swfRequest;

    //TODO
    protected $preRequest = false;

    protected $requestTraceConfig = [];

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function next()
    {
        while (isset($this->requests[$this->cursor])) {
            $request = $this->requests[$this->cursor];
            $requestTraceConfig = $this->requestTraceConfig[$this->cursor];
            $response = (new HttpClient())->send(
                $request, $this->swfRequest, $requestTraceConfig['span_name'] ?? null,
                $requestTraceConfig['inject_span_ctx'] ?? true,
                $requestTraceConfig['flushing_trace'] ?? false,
                $requestTraceConfig['with_trace'] ?? false
            );
            ++$this->cursor;
            yield $response;
        }
    }

    public function close()
    {
        // TODO: Implement close() method.
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
        $request = SaberGM::psr()->withMethod($method)
            ->withUri(new Uri($url));

        if (!is_null($body)) {
            switch ($bodyType) {
                case Client::JSON_BODY:
                    $headers['Content-Type'] = ContentType::JSON;
                    $body = json_encode($body);
                    break;
                case Client::FORM_BODY:
                    $headers['Content-Type'] = ContentType::URLENCODE;
                    $body = http_build_query($bodyType);
                    break;
                case Client::STRING_BODY:
                    $body = (string)$body;
                    break;
            }
            $bufferStream = new BufferStream($bodyLength ?? strlen($body));
            $bufferStream->write($body);
            $request->withBody($bufferStream);
        }
        $request->withHeaders($headers);
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
