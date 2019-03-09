<?php

namespace App\components\http;

use App\components\zipkin\HttpClient;
use Swlib\Http\BufferStream;
use Swlib\Http\Uri;
use Swlib\SaberGM;
use Swoole\Coroutine\Channel;

class Client
{
    const JSON_BODY = 'json';
    const FORM_BODY = 'form';
    const STRING_BODY = 'string';

    public static function get($url, $swfRequest, $headers = [])
    {
        return static::multiGet($url, $swfRequest, $headers);
    }

    public static function post($url, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiPost($url, $swfRequest, $headers, $body, $bodyType);
    }

    public static function put($url, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiPut($url, $swfRequest, $headers, $body, $bodyType);
    }

    public static function delete($url, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiDelete($url, $swfRequest, $headers, $body, $bodyType);
    }

    public static function multiGet($urls, $swfRequest, $headers = [])
    {
        return static::multiRequest($urls, 'GET', $swfRequest, $headers);
    }

    public static function multiPost($urls, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiRequest($urls, 'POST', $swfRequest, $headers, $body, $bodyType);
    }

    public static function multiPut($urls, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiRequest($urls, 'PUT', $swfRequest, $headers, $body, $bodyType);
    }

    public static function multiDelete($urls, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        return static::multiRequest($urls, 'DELETE', $swfRequest, $headers, $body, $bodyType);
    }

    public static function multiRequest($urls, $method, $swfRequest, $headers = [], $body = null, $bodyType = self::JSON_BODY)
    {
        if (!is_array($urls)) {
            $urls = (array)$urls;
        }

        $requestCount = count($urls);

        $chan = new Channel($requestCount);

        $aggResult = [];
        foreach ($urls as $id => $url) {
            go(
                function () use (&$aggResult, $id, $url, $chan, $method, $headers, $body, $bodyType, $swfRequest) {
                    $request = SaberGM::psr()->withMethod($method)
                        ->withUri(new Uri($url));

                    if (!is_null($body)) {
                        switch ($bodyType) {
                            case self::JSON_BODY:
                                $headers['Content-Type'] = 'application/json';
                                $body = json_encode($body);
                                break;
                            case self::FORM_BODY:
                                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
                                $body = http_build_query($bodyType);
                                break;
                            case self::STRING_BODY:
                                $body = (string)$body;
                                break;
                        }
                        $request->withBody(new BufferStream($body));
                    }

                    foreach ($headers as $name => $value) {
                        $request->withAddedHeader($name, $value);
                    }

                    $aggResult[$id] = (new HttpClient())->send($request, $swfRequest);

                    $chan->push(1);
                }
            );
        }

        for ($i = 0; $i < $requestCount; ++$i) {
            $chan->pop();
        }
        $chan->close();

        return $aggResult;
    }
}
