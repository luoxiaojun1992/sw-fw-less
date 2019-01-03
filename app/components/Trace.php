<?php

namespace App\components;

use Swoole\Coroutine\Http\Client;

class Trace
{
    private static $instance;

    private $zipkinUrl;

    public static function create($zipkinUrl = null)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (Config::get('trace.switch')) {
            return self::$instance = new self($zipkinUrl);
        } else {
            return null;
        }
    }

    public function __construct($zipkinUrl)
    {
        $this->zipkinUrl = $zipkinUrl;
    }

    public function span($options, $callback)
    {
        $serviceName = isset($options['service_name']) ? $options['service_name'] : 'sw-fw-less';
        $spanName = isset($options['span_name']) ? $options['span_name'] : 'request';
        $traceId = isset($options['trace_id']) ? $options['trace_id'] : null;
        $parentSpanId = isset($options['parent_span']) ? $options['parent_span'] : null;

        $startTime = (int)((float) (new \DateTime('now'))->format('U.u') * 1000 * 1000);

        call_user_func($callback);

        $endTime = (int)((float) (new \DateTime('now'))->format('U.u') * 1000 * 1000);

        $spans = array (
            0 =>
                array (
                    'traceId' => str_pad($traceId ? : str_replace('-', '', \Ramsey\Uuid\Uuid::uuid4()), 32, '0', STR_PAD_LEFT),
                    'name' => $spanName,
                    'parentId' => $parentSpanId,
                    'id' => str_pad(dechex(mt_rand()), 16, '0', STR_PAD_LEFT),
                    'timestamp' => $startTime,
                    'duration' => $endTime - $startTime,
                    'debug' => false,
                    'shared' => true,
                    'localEndpoint' =>
                        array (
                            'serviceName' => $serviceName,
                        ),
                    'tags' => new \stdClass(),
                ),
        );

        $json = json_encode($spans);

        try {
            $info = parse_url($this->zipkinUrl);
            $host = $info['host'];
            $schema = isset($info['scheme']) ? $info['scheme'] : 'http';
            $isSsl = $schema === 'https';
            $port = isset($info['port']) ? $info['port'] : ($isSsl ? 443 : 80);
            $path = isset($info['path']) ? $info['path'] : '/';
            if (isset($info['query'])) {
                $path .= ('?' . $info['query']);
            }
            $client = new Client($host, $port, $isSsl);
            $client->setHeaders(['Content-Type' => 'application/json']);
            $client->post($path, $json);
        } catch (\Exception $e) {
            //
        }
    }
}
