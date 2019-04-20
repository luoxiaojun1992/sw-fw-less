<?php

namespace SwFwLess\components\zipkin;

use SwFwLess\facades\Log;
use SwFwLess\facades\RedisPool;
use RuntimeException;
use Zipkin\Recording\Span;
use Zipkin\Reporter;
use Zipkin\Reporters\Metrics;
use Zipkin\Reporters\NoopMetrics;

final class RedisReporter implements Reporter
{
    const DEFAULT_OPTIONS = [
        'queue_name' => 'queue:zipkin:span',
        'connection' => 'zipkin',
    ];

    /**
     * @var array
     */
    private $options;

    /**
     * @var Metrics
     */
    private $reportMetrics;

    public function __construct(
        array $options = [],
        Metrics $reporterMetrics = null
    ) {
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
        $this->reportMetrics = $reporterMetrics ?: new NoopMetrics();
    }

    /**
     * @param Span[] $spans
     * @return void
     * @throws \Exception
     */
    public function report(array $spans)
    {
        $payload = json_encode(array_map(function (Span $span) {
            return $span->toArray();
        }, $spans));

        $this->reportMetrics->incrementSpans(count($spans));
        $this->reportMetrics->incrementMessages();

        $payloadLength = strlen($payload);
        $this->reportMetrics->incrementSpanBytes($payloadLength);
        $this->reportMetrics->incrementMessageBytes($payloadLength);

        try {
            $this->enqueue($payload);
        } catch (RuntimeException $e) {
            $this->reportMetrics->incrementSpansDropped(count($spans));
            $this->reportMetrics->incrementMessagesDropped($e);
        }
    }

    /**
     * @param $payload
     * @throws \Throwable
     */
    private function enqueue($payload)
    {
        /** @var \Redis $redisClient */
        $redisClient = $this->getRedisClient();
        if (is_null($redisClient)) {
            Log::error('Zipkin report error: redis client is null');
            return;
        }

        if (empty($this->options['queue_name'])) {
            Log::error('Zipkin report error: redis queue name is empty');
            return;
        }

        try {
            $redisClient->lpush($this->options['queue_name'], $payload);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redisClient);
        }
    }

    private function getRedisClient()
    {
        if (!empty($this->options['connection'])) {
            return RedisPool::pick($this->options['connection']);
        }

        return null;
    }
}
