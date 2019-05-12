<?php

namespace SwFwLess\components\zipkin;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Zipkin\Span;
use const Zipkin\Tags\ERROR;
use const Zipkin\Tags\HTTP_HOST;
use const Zipkin\Tags\HTTP_METHOD;
use const Zipkin\Tags\HTTP_PATH;
use const Zipkin\Tags\HTTP_STATUS_CODE;

/**
 * Class Middleware
 * @package SwFwLess\components\zipkin
 */
class Middleware extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @return bool
     */
    private function needSample(Request $request)
    {
        $path = $request->uri();
        $apiPrefix = explode(',', config('zipkin.api_prefix', '/'));
        foreach ($apiPrefix as $prefix) {
            if (stripos($path, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @return \SwFwLess\components\http\Response
     * @throws \Throwable
     */
    public function handle(Request $request)
    {
        if (!$this->needSample($request)) {
            return $this->next();
        }

        /** @var Tracer $swfTracer */
        $swfTracer = $request->getTracer();
        $psrRequest = $request->convertToPsr7();
        $path = $psrRequest->getUri()->getPath();
        return $swfTracer->serverSpan($this->getSpanName($swfTracer, $request, $psrRequest), function (Span $span) use ($request, $psrRequest, $swfTracer, $path) {
            if ($span->getContext()->isSampled()) {
                $swfTracer->addTag($span, HTTP_HOST, $psrRequest->getUri()->getHost());
                $swfTracer->addTag($span, HTTP_PATH, $path);
                $swfTracer->addTag($span, Tracer::HTTP_QUERY_STRING, (string)$psrRequest->getUri()->getQuery());
                $swfTracer->addTag($span, HTTP_METHOD, $psrRequest->getMethod());
                $httpRequestBodyLen = $psrRequest->getBody()->getSize();
                $swfTracer->addTag($span, Tracer::HTTP_REQUEST_BODY_SIZE, $httpRequestBodyLen);
                $swfTracer->addTag($span, Tracer::HTTP_REQUEST_BODY, $swfTracer->formatHttpBody(
                    $psrRequest->getBody()->getContents(),
                    $httpRequestBodyLen
                ));
                $psrRequest->getBody()->seek(0);
                $swfTracer->addTag($span, Tracer::HTTP_REQUEST_HEADERS, json_encode($psrRequest->getHeaders(), JSON_UNESCAPED_UNICODE));
                $swfTracer->addTag(
                    $span,
                    Tracer::HTTP_REQUEST_PROTOCOL_VERSION,
                    $swfTracer->formatHttpProtocolVersion($psrRequest->getProtocolVersion())
                );
                $swfTracer->addTag($span, Tracer::HTTP_REQUEST_SCHEME, $psrRequest->getUri()->getScheme());
            }

            /** @var Response $response */
            $response = null;
            try {
                $response = $this->next();

                if ($span->getContext()->isSampled()) {
                    if ($response->isServerError()) {
                        $swfTracer->addTag($span, ERROR, 'server error');
                    } elseif ($response->isClientError()) {
                        $swfTracer->addTag($span, ERROR, 'client error');
                    }
                }

                return $response;
            } catch (\Throwable $e) {
                throw $e;
            } finally {
                $isSampled = $span->getContext()->isSampled();
                if ($isSampled) {
                    $span->setName($this->getSpanName($swfTracer, $request, $psrRequest));
                }
                if ($response) {
                    if ($span->getContext()->isSampled()) {
                        $psrResponse = $response->convertToPsr7();
                        $swfTracer->addTag($span, HTTP_STATUS_CODE, $psrResponse->getStatusCode());
                        $httpResponseBodyLen = $psrResponse->getBody()->getSize();
                        $swfTracer->addTag($span, Tracer::HTTP_RESPONSE_BODY_SIZE, $httpResponseBodyLen);
                        $swfTracer->addTag($span, Tracer::HTTP_RESPONSE_BODY, $swfTracer->formatHttpBody(
                            $psrResponse->getBody()->getContents(),
                            $httpResponseBodyLen
                        ));
                        $psrResponse->getBody()->seek(0);
                        $swfTracer->addTag($span, Tracer::HTTP_RESPONSE_HEADERS, json_encode($response->getHeaders(), JSON_UNESCAPED_UNICODE));
                        $swfTracer->addTag(
                            $span,
                            Tracer::HTTP_RESPONSE_PROTOCOL_VERSION,
                            $swfTracer->formatHttpProtocolVersion($psrRequest->getProtocolVersion())
                        );
                    }
                }
            }
        }, true);
    }

    /**
     * @param Tracer $swfTracer
     * @param Request $request
     * @param ServerRequestInterface $psrRequest
     * @return string
     */
    private function getSpanName(Tracer $swfTracer, Request $request, ServerRequestInterface $psrRequest)
    {
        if ($request->getRoute()) {
            $spanName = $swfTracer->formatHttpPath($request->getRoute());
        } else {
            $spanName = $swfTracer->formatRoutePath($psrRequest->getUri()->getPath());
        }

        return $spanName;
    }
}
