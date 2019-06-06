<?php

namespace SwFwLess\components\zipkin;

use SwFwLess\components\http\Request;
use SwFwLess\facades\Log;
use Psr\Http\Message\RequestInterface;
use Zipkin\Endpoint;
use const Zipkin\Kind\CLIENT;
use const Zipkin\Kind\SERVER;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\RequestHeaders;
use Zipkin\Propagation\TraceContext;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Span;
use const Zipkin\Tags\ERROR;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;

/**
 * Class Tracer
 * @package SwFwLess\components\zipkin
 */
class Tracer
{
    const HTTP_REQUEST_BODY = 'http.request.body';
    const HTTP_REQUEST_BODY_SIZE = 'http.request.body.size';
    const HTTP_REQUEST_HEADERS = 'http.request.headers';
    const HTTP_REQUEST_PROTOCOL_VERSION = 'http.request.protocol.version';
    const HTTP_REQUEST_SCHEME = 'http.request.scheme';
    const HTTP_RESPONSE_BODY = 'http.response.body';
    const HTTP_RESPONSE_BODY_SIZE = 'http.response.body.size';
    const HTTP_RESPONSE_HEADERS = 'http.response.headers';
    const HTTP_RESPONSE_PROTOCOL_VERSION = 'http.response.protocol.version';
    const RUNTIME_START_SYSTEM_LOAD = 'runtime.start_system_load';
    const RUNTIME_FINISH_SYSTEM_LOAD = 'runtime.finish_system_load';
    const RUNTIME_MEMORY = 'runtime.memory';
    const RUNTIME_PHP_VERSION = 'runtime.php.version';
    const RUNTIME_PHP_SAPI = 'runtime.php.sapi';
    const FRAMEWORK_VERSION = 'framework.version';
    const HTTP_QUERY_STRING = 'http.query_string';

    private $serviceName = 'Sw-Fw-Less';
    private $endpointUrl = 'http://localhost:9411/api/v2/spans';
    private $sampleRate = 0;
    private $bodySize = 500;
    private $curlTimeout = 1;
    private $redisOptions = [
        'queue_name' => 'queue:zipkin:span',
        'connection' => 'zipkin',
    ];
    private $reportType = 'http';

    /** @var \Zipkin\Tracer */
    private $tracer;

    /** @var Tracing */
    private $tracing;

    /** @var array TraceContext[] */
    private $contextStack = [];

    /** @var Request */
    private $request;

    /**
     * Tracer constructor.
     */
    public function __construct()
    {
        $this->serviceName = config('zipkin.service_name', 'Sw-Fw-Less');
        $this->endpointUrl = config('zipkin.endpoint_url', 'http://localhost:9411/api/v2/spans');
        $this->sampleRate = config('zipkin.sample_rate', 0);
        $this->bodySize = config('zipkin.body_size', 5000);
        $this->curlTimeout = config('zipkin.curl_timeout', 1);
        $this->redisOptions = array_merge($this->redisOptions, config('zipkin.redis_options', []));
        $this->reportType = config('zipkin.report_type', 'http');

        $this->createTracer();
    }

    /**
     * Create zipkin tracer
     */
    private function createTracer()
    {
        $endpoint = Endpoint::create(
            $this->serviceName,
            array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
            null,
            array_key_exists('REMOTE_PORT', $_SERVER) ? (int)$_SERVER['REMOTE_PORT'] : null
        );
        $sampler = BinarySampler::createAsAlwaysSample();
        $this->tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($this->getReporter())
            ->build();
        $this->tracer = $this->getTracing()->getTracer();
    }

    private function getReporter()
    {
        if ($this->reportType === 'redis') {
            return new RedisReporter($this->redisOptions);
        } elseif ($this->reportType === 'http') {
            return new HttpReporter(['endpoint_url' => $this->endpointUrl, 'timeout' => $this->curlTimeout]);
        }

        return new HttpReporter(['endpoint_url' => $this->endpointUrl, 'timeout' => $this->curlTimeout]);
    }

    /**
     * @return Tracing
     */
    public function getTracing()
    {
        return $this->tracing;
    }

    /**
     * @return \Zipkin\Tracer
     */
    public function getTracer()
    {
        return $this->tracer;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Create a server trace
     *
     * @param $name
     * @param $callback
     * @param bool $flush
     * @return mixed
     * @throws \Throwable
     */
    public function serverSpan($name, $callback, $flush = false)
    {
        return $this->span($name, $callback, SERVER, $flush);
    }

    /**
     * Create a client trace
     *
     * @param $name
     * @param $callback
     * @param bool $flush
     * @return mixed
     * @throws \Throwable
     */
    public function clientSpan($name, $callback, $flush = false)
    {
        return $this->span($name, $callback, CLIENT, $flush);
    }

    /**
     * Create a trace
     *
     * @param string $name
     * @param callable $callback
     * @param null|string $kind
     * @param bool $flush
     * @return mixed
     * @throws \Throwable
     */
    public function span($name, $callback, $kind = null, $flush = false)
    {
        $parentContext = $this->getParentContext();
        $span = $this->getSpan($parentContext);
        $span->setName($name);
        if ($kind) {
            $span->setKind($kind);
        }

        $span->start();

        $spanContext = $span->getContext();
        array_push($this->contextStack, $spanContext);

        //Memory tags
        $startMemory = 0;
        if ($span->getContext()->isSampled()) {
            $startMemory = memory_get_usage();
            $this->beforeSpanTags($span);
        }

        try {
            return call_user_func_array($callback, ['span' => $span]);
        } catch (\Throwable $e) {
            if ($span->getContext()->isSampled()) {
                $this->addTag($span, ERROR, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
            throw $e;
        } finally {
            if ($span->getContext()->isSampled()) {
                //Memory tags
                $this->addTag($span, static::RUNTIME_MEMORY, round((memory_get_usage() - $startMemory) / 1000000, 2) . 'MB');

                $this->afterSpanTags($span);
            }

            $span->finish();
            array_pop($this->contextStack);

            if ($flush) {
                $this->flushTracer();
            }
        }
    }

    /**
     * Formatting http protocol version
     *
     * @param $protocolVersion
     * @return string
     */
    public function formatHttpProtocolVersion($protocolVersion)
    {
        if (stripos($protocolVersion, 'HTTP/') !== 0) {
            return 'HTTP/' . $protocolVersion;
        }

        return strtoupper($protocolVersion);
    }

    /**
     * Formatting http body
     *
     * @param $httpBody
     * @param null $bodySize
     * @return string
     */
    public function formatHttpBody($httpBody, $bodySize = null)
    {
        $httpBody = $this->convertToStr($httpBody);

        if (is_null($bodySize)) {
            $bodySize = strlen($httpBody);
        }

        if ($bodySize > $this->bodySize) {
            $httpBody = mb_substr($httpBody, 0, $this->bodySize, 'utf8') . ' ...';
        }

        return $httpBody;
    }

    /**
     * Formatting http path
     *
     * @param $httpPath
     * @return string
     */
    public function formatHttpPath($httpPath)
    {
        if (strpos($httpPath, '/') !== 0) {
            $httpPath = '/' . $httpPath;
        }

        return $httpPath;
    }

    /**
     * Formatting http path
     *
     * @param $httpPath
     * @return string|string[]|null
     */
    public function formatRoutePath($httpPath)
    {
        $httpPath = preg_replace('/\/\d+$/', '/{id}', $httpPath);
        $httpPath = preg_replace('/\/\d+\//', '/{id}/', $httpPath);

        return $httpPath;
    }

    /**
     * Add span tag
     *
     * @param Span $span
     * @param $key
     * @param $value
     */
    public function addTag($span, $key, $value)
    {
        $span->tag($key, $this->convertToStr($value));
    }

    /**
     * Convert variable to string
     *
     * @param $value
     * @return string
     */
    public function convertToStr($value)
    {
        if (!is_scalar($value)) {
            $value = '';
        } else {
            $value = (string)$value;
        }

        return $value;
    }

    /**
     * Inject trace context to psr request
     *
     * @param TraceContext $context
     * @param RequestInterface $request
     */
    public function injectContextToRequest($context, &$request)
    {
        $injector = $this->getTracing()->getPropagation()->getInjector(new RequestHeaders());
        $injector($context, $request);
    }

    /**
     * Extract trace context from sw-fw-less request
     *
     * @param Request $request
     * @return TraceContext|DefaultSamplingFlags
     */
    public function extractRequestToContext($request)
    {
        $extractor = $this->getTracing()->getPropagation()->getExtractor(new SwfRequestHeaders());
        return $extractor($request);
    }

    /**
     * @return TraceContext|DefaultSamplingFlags|null
     */
    private function getParentContext()
    {
        $parentContext = null;
        $contextStackLen = count($this->contextStack);
        if ($contextStackLen > 0) {
            $parentContext = $this->contextStack[$contextStackLen - 1];
        } else {
            //Extract trace context from sw-fw-less request
            $parentContext = $this->extractRequestToContext($this->getRequest());
        }

        return $parentContext;
    }

    /**
     * @param TraceContext|DefaultSamplingFlags $parentContext
     * @return \Zipkin\Span
     */
    private function getSpan($parentContext)
    {
        $tracer = $this->getTracer();

        if (!$parentContext) {
            $span = $tracer->newTrace($this->getDefaultSamplingFlags());
        } else {
            if ($parentContext instanceof TraceContext) {
                $span = $tracer->newChild($parentContext);
            } else {
                if (is_null($parentContext->isSampled())) {
                    $samplingFlags = $this->getDefaultSamplingFlags();
                } else {
                    $samplingFlags = $parentContext;
                }

                $span = $tracer->newTrace($samplingFlags);
            }
        }

        return $span;
    }

    /**
     * @return DefaultSamplingFlags
     */
    private function getDefaultSamplingFlags()
    {
        $sampleRate = $this->sampleRate;
        if ($sampleRate >= 1) {
            $samplingFlags = DefaultSamplingFlags::createAsEmpty(); //Sample config determined by sampler
        } elseif ($sampleRate <= 0) {
            $samplingFlags = DefaultSamplingFlags::createAsNotSampled();
        } else {
            mt_srand(time());
            if (mt_rand() / mt_getrandmax() <= $sampleRate) {
                $samplingFlags = DefaultSamplingFlags::createAsEmpty(); //Sample config determined by sampler
            } else {
                $samplingFlags = DefaultSamplingFlags::createAsNotSampled();
            }
        }

        return $samplingFlags;
    }

    /**
     * @param Span $span
     */
    private function startSysLoadTag($span)
    {
        //Not supported in windows os
        if (!function_exists('sys_getloadavg')) {
            return;
        }

        $startSystemLoad = sys_getloadavg();
        foreach ($startSystemLoad as $k => $v) {
            $startSystemLoad[$k] = round($v, 2);
        }
        $this->addTag($span, static::RUNTIME_START_SYSTEM_LOAD, implode(',', $startSystemLoad));
    }

    /**
     * @param Span $span
     */
    private function finishSysLoadTag($span)
    {
        //Not supported in windows os
        if (!function_exists('sys_getloadavg')) {
            return;
        }

        $finishSystemLoad = sys_getloadavg();
        foreach ($finishSystemLoad as $k => $v) {
            $finishSystemLoad[$k] = round($v, 2);
        }
        $this->addTag($span, static::RUNTIME_FINISH_SYSTEM_LOAD, implode(',', $finishSystemLoad));
    }

    /**
     * @param Span $span
     */
    private function beforeSpanTags($span)
    {
        $this->addTag($span, self::FRAMEWORK_VERSION, 'Sw-Fw-Less-' . appVersion());
        $this->addTag($span, self::RUNTIME_PHP_VERSION, PHP_VERSION);
        $this->addTag($span, self::RUNTIME_PHP_SAPI, sapi());

        $this->startSysLoadTag($span);
    }

    /**
     * @param Span $span
     */
    private function afterSpanTags($span)
    {
        $this->finishSysLoadTag($span);
    }

    private function flushTracer()
    {
        try {
            if ($tracer = $this->getTracer()) {
                $tracer->flush();
            }
        } catch (\Throwable $e) {
            Log::error('Zipkin report error ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->flushTracer();
    }
}
