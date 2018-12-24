<?php

namespace App\components\es;

use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use GuzzleHttp\Ring\Exception\RingException;

/**
 * Class GuzzleCoHandler
 *
 * @inheritdoc
 *
 * Http handler that uses Swoole Coroutine as a transport layer.
 *
 * @author https://github.com/limingxinleo/guzzle-swoole-handler
 *
 * @package App\components\es
 */
class GuzzleCoHandler
{
    /**
     * Swoole 协程 Http 客户端
     *
     * @var \Swoole\Coroutine\Http\Client
     */
    private $client;

    /**
     * 配置选项
     *
     * @var array
     */
    private $settings = [];

    private $btime;

    private $effectiveUrl = '';

    private $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function __invoke($request)
    {
        $method = isset($request['http_method']) ? $request['http_method'] : 'GET';
        $scheme = isset($request['scheme']) ? $request['scheme'] : 'http';
        $ssl = 'https' === $scheme;
        $body = isset($request['body']) ? $request['body'] : '';
        $this->effectiveUrl = Core::url($request);
        $params = parse_url($this->effectiveUrl);
        $host = $params['host'];
        if (!isset($params['port'])) {
            $params['port'] = $ssl ? 443 : 80;
        }
        $port = $params['port'];
        $path = isset($params['path']) ? $params['path'] : '/';
        if (!empty($params['query'])) {
            $path .= ('?' . $params['query']);
        }

        $this->client = new Client($host, $port, $ssl);
        $this->client->setMethod($method);
        $this->client->setData($body);

        // 初始化Headers
        $this->initHeaders($request);
        $this->initSettings($this->options);

        // 设置客户端参数
        if (!empty($this->settings)) {
            $this->client->set($this->settings);
        }

        $this->btime = microtime(true);
        $this->client->execute($path);
        $this->client->close();

        return $this->getResponse();
    }

    protected function initSettings($options)
    {
        if (isset($options['delay'])) {
            Coroutine::sleep((float)$options['delay'] / 1000);
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $this->settings['timeout'] = $options['timeout'];
        }
    }

    protected function initHeaders($request)
    {
        $headers = [];
        foreach (isset($request['headers']) ? $request['headers'] : [] as $name => $value) {
            $headers[$name] = implode(',', $value);
        }

        $clientConfig = isset($request['client']['curl']) ? $request['client']['curl'] : [];
        if (isset($clientConfig[CURLOPT_USERPWD])) {
            $userInfo = $clientConfig[CURLOPT_USERPWD];
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($userInfo));
        }

        $this->client->setHeaders($headers);
    }

    protected function getResponse()
    {
        $ex = $this->checkStatusCode();
        if ($ex !== true) {
            return new CompletedFutureArray([
                'reason' => null,
                'error' => $ex,
                'transfer_stats' => [
                    'total_time' => microtime(true) - $this->btime,
                ],
                'effective_url' => $this->effectiveUrl,
                'headers' => isset($this->client->headers) ? $this->client->headers : [],
                'status' => $this->client->statusCode,
                'body' => fopen('data://text/plain,' . $this->client->body, 'r'),
                'curl' => [
                    'errno' => $this->client->errCode,
                ]
            ]);
        } else {
            return new CompletedFutureArray([
                'transfer_stats' => [
                    'total_time' => microtime(true) - $this->btime,
                ],
                'effective_url' => $this->effectiveUrl,
                'headers' => isset($this->client->headers) ? $this->client->headers : [],
                'status' => $this->client->statusCode,
                'body' => fopen('data://text/plain,' . $this->client->body, 'r')
            ]);
        }
    }

    protected function checkStatusCode()
    {
        $statusCode = $this->client->statusCode;
        $errCode = $this->client->errCode;

        if ($errCode === 110) {
            $this->client->errCode = 28;
        } elseif ($errCode === 111) {
            $this->client->errCode = 7;
        } elseif ($errCode === 113) {
            $this->client->errCode = 6;
        } else {
            $this->client->errCode = -1;
        }

        if ($statusCode === -1) {
            return new RingException(sprintf('Connection timed out errCode=%s', $errCode));
        } elseif ($statusCode === -2) {
            return new RingException('Request timed out');
        } elseif ($statusCode === -3) {
            return new RingException('Connection refused');
        }

        return true;
    }
}
