<?php

namespace SwFwLess\components\zipkin;

use RuntimeException;
use Swlib\SaberGM;
use Zipkin\Recording\Span;
use Zipkin\Reporter;
use Zipkin\Reporters\Metrics;
use Zipkin\Reporters\NoopMetrics;

final class HttpReporter implements Reporter
{
    const DEFAULT_OPTIONS = [
        'endpoint_url' => 'http://localhost:9411/api/v2/spans',
        'headers' => [
            'Content-Type' => 'application/json',
        ],
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
     */
    public function report(array $spans)
    {
        //todo json depth
        $payload = json_encode(array_map(function (Span $span) {
            return $span->toArray();
        }, $spans));

        $this->reportMetrics->incrementSpans(count($spans));
        $this->reportMetrics->incrementMessages();

        $payloadLength = strlen($payload);
        $this->reportMetrics->incrementSpanBytes($payloadLength);
        $this->reportMetrics->incrementMessageBytes($payloadLength);

        try {
            $this->sendToZipkin($payload);
        } catch (RuntimeException $e) {
            $this->reportMetrics->incrementSpansDropped(count($spans));
            $this->reportMetrics->incrementMessagesDropped($e);
        }
    }

    private function sendToZipkin($payload)
    {
        try {
            $this->options['headers'] = array_merge($this->options['headers'], [
                'Content-Length' => strlen($payload),
            ]);
            SaberGM::post($this->options['endpoint_url'], $payload, $this->options);
        } catch (\Throwable $e) {
            throw new RuntimeException($e);
        }
    }
}
