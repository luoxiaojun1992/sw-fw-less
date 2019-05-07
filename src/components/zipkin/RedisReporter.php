<?php

namespace SwFwLess\components\zipkin;

use SwFwLess\facades\Log;
use SwFwLess\facades\RedisPool;
use RuntimeException;
use Zipkin\Recording\Span;
use Zipkin\Reporter;

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

    public function __construct(
        array $options = []
    ) {
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    /**
     * @param Span[] $spans
     * @return void
     * @throws \Exception
     */
    public function report(array $spans)
    {
        if (!$spans) {
            return;
        }

        $payload = json_encode(array_map(function (Span $span) {
            return $span->toArray();
        }, $spans));

        try {
            $this->enqueue($payload);
        } catch (RuntimeException $e) {
            //
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
