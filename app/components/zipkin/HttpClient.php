<?php

namespace App\components\zipkin;

use App\components\http\Request;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Zipkin\Span;
use const Zipkin\Tags\HTTP_HOST;
use const Zipkin\Tags\HTTP_METHOD;
use const Zipkin\Tags\HTTP_PATH;
use const Zipkin\Tags\HTTP_STATUS_CODE;

/**
 * Class HttpClient
 * @package App\components\zipkin
 */
class HttpClient extends GuzzleHttpClient
{
    /**
     * Send http request with zipkin trace
     *
     * @param RequestInterface $request
     * @param array $options
     * @param string $spanName
     * @param Request $swfRequest
     * @return mixed|\Psr\Http\Message\ResponseInterface|null
     * @throws \Exception
     */
    public function send(RequestInterface $request, array $options = [], $spanName = null, $swfRequest = null)
    {
        /** @var Tracer $swfTracer */
        $swfTracer = $swfRequest->getTracer();
        $path = $request->getUri()->getPath();

        return $swfTracer->span(
            isset($spanName) ? $spanName : $swfTracer->formatRoutePath($path),
            function (Span $span) use ($request, $options, $swfTracer, $path) {
                //Inject trace context to api psr request
                $swfTracer->injectContextToRequest($span->getContext(), $request);

                if ($span->getContext()->isSampled()) {
                    $swfTracer->addTag($span, HTTP_HOST, $request->getUri()->getHost());
                    $swfTracer->addTag($span, HTTP_PATH, $path);
                    $swfTracer->addTag($span, Tracer::HTTP_QUERY_STRING, (string)$request->getUri()->getQuery());
                    $swfTracer->addTag($span, HTTP_METHOD, $request->getMethod());
                    $httpRequestBodyLen = $request->getBody()->getSize();
                    $swfTracer->addTag($span, Tracer::HTTP_REQUEST_BODY_SIZE, $httpRequestBodyLen);
                    $swfTracer->addTag($span, Tracer::HTTP_REQUEST_BODY, $swfTracer->formatHttpBody($request->getBody()->getContents(), $httpRequestBodyLen));
                    $request->getBody()->seek(0);
                    $swfTracer->addTag($span, Tracer::HTTP_REQUEST_HEADERS, json_encode($request->getHeaders(), JSON_UNESCAPED_UNICODE));
                    $swfTracer->addTag(
                        $span,
                        Tracer::HTTP_REQUEST_PROTOCOL_VERSION,
                        $swfTracer->formatHttpProtocolVersion($request->getProtocolVersion())
                    );
                    $swfTracer->addTag($span, Tracer::HTTP_REQUEST_SCHEME, $request->getUri()->getScheme());
                }

                $response = null;
                try {
                    $response = parent::send($request, $options);
                    return $response;
                } catch (\Exception $e) {
                    Log::error('CURL ERROR ' . $e->getMessage());
                    throw new \Exception('CURL ERROR ' . $e->getMessage());
                } finally {
                    if ($response) {
                        if ($span->getContext()->isSampled()) {
                            $swfTracer->addTag($span, HTTP_STATUS_CODE, $response->getStatusCode());
                            $httpResponseBodyLen = $response->getBody()->getSize();
                            $swfTracer->addTag($span, Tracer::HTTP_RESPONSE_BODY_SIZE, $httpResponseBodyLen);
                            $swfTracer->addTag($span, Tracer::HTTP_RESPONSE_BODY, $swfTracer->formatHttpBody($response->getBody()->getContents(), $httpResponseBodyLen));
                            $response->getBody()->seek(0);
                            $swfTracer->addTag($span, Tracer::HTTP_RESPONSE_HEADERS, json_encode($response->getHeaders(), JSON_UNESCAPED_UNICODE));
                            $swfTracer->addTag(
                                $span,
                                Tracer::HTTP_RESPONSE_PROTOCOL_VERSION,
                                $swfTracer->formatHttpProtocolVersion($response->getProtocolVersion())
                            );
                        }
                    }
                }
            }, null, \Zipkin\Kind\CLIENT);
    }
}
