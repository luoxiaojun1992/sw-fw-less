<?php

namespace SwFwLess\components\volcano\http;

use SwFwLess\components\http\Client;
use SwFwLess\components\volcano\OperatorInterface;
use SwFwLess\components\zipkin\HttpClient;
use Swlib\Http\BufferStream;
use Swlib\Http\ContentType;
use Swlib\Saber\Request;
use Swlib\Http\Uri;
use Swlib\SaberGM;

class HttpRequest implements OperatorInterface
{
    /** @var Request[] */
    protected $requests = [];

    protected $cursor = 0;

    protected $swfRequest;

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function next()
    {
        $request = $this->requests[$this->cursor];
        $response = (new HttpClient())->send($request, $this->swfRequest);
        ++$this->cursor;
        return $response;
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

    public function addRequest($url, $method, $headers = [], $body = null, $bodyType = Client::JSON_BODY)
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
            $request->withBody(new BufferStream($body));
        }
        $request->withHeaders($headers);
        $this->requests[] = $request;
    }
}
