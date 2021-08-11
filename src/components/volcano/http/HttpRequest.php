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

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function next()
    {
        while (isset($this->requests[$this->cursor])) {
            $request = $this->requests[$this->cursor];
            $response = (new HttpClient())->send($request, $this->swfRequest);
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
        $url, $method, $headers = [], $body = null, $bodyType = Client::JSON_BODY, $bodyLength = null
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
    }

    public function info()
    {
        $requests = [];
        foreach ($this->requests as $request) {
            $body = $request->getBody();
            $body = clone $body;
            $bodyContents = $body->getContents();
            $bodyLength = strlen($bodyContents);
            $requests[] = [
                'url' => (string)($request->getUri()),
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'body' => $bodyContents,
                'body_length' => $bodyLength,
            ];
        }

        return [
            'pre_request' => $this->preRequest,
            'request_count' => count($this->requests),
            'requests' => $requests,
        ];
    }
}
