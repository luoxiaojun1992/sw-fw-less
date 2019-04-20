<?php

namespace SwFwLess\components\storage\qiniu;

use Qiniu\Config;
use Qiniu\Http\Request;
use Qiniu\Http\Response;
use Swoole\Coroutine\Http\Client;

final class QiniuCoHttpClient
{
    public static function get($url, array $headers = array())
    {
        $request = new Request('GET', $url, $headers);
        return self::sendRequest($request);
    }

    public static function delete($url, array $headers = array())
    {
        $request = new Request('DELETE', $url, $headers);
        return self::sendRequest($request);
    }

    public static function post($url, $body, array $headers = array())
    {
        $request = new Request('POST', $url, $headers, $body);
        return self::sendRequest($request);
    }

    public static function multipartPost(
        $url,
        $fields,
        $name,
        $fileName,
        $fileBody,
        $mimeType = null,
        array $headers = array()
    ) {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $key => $val) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$key\"");
            array_push($data, '');
            array_push($data, $val);
        }

        array_push($data, '--' . $mimeBoundary);
        $finalMimeType = empty($mimeType) ? 'application/octet-stream' : $mimeType;
        $finalFileName = self::escapeQuotes($fileName);
        array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$finalFileName\"");
        array_push($data, "Content-Type: $finalMimeType");
        array_push($data, '');
        array_push($data, $fileBody);

        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;
        $headers['Content-Type'] = $contentType;
        $request = new Request('POST', $url, $headers, $body);
        return self::sendRequest($request);
    }

    private static function userAgent()
    {
        $sdkInfo = "QiniuPHP/" . Config::SDK_VER;

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        $ua = "$sdkInfo $envInfo PHP/$phpVer";
        return $ua;
    }

    public static function sendRequest($request)
    {
        $t1 = microtime(true);
        $urlInfo = parse_url($request->url);
        $scheme = isset($urlInfo['scheme']) ? $urlInfo['scheme'] : 'http';
        $ssl = 'https' === $scheme;
        $host = $urlInfo['host'];
        if (!isset($urlInfo['port'])) {
            $port = $ssl ? 443 : 80;
        } else {
            $port = $urlInfo['port'];
        }
        $path = isset($urlInfo['path']) ? $urlInfo['path'] : '/';
        if (!empty($urlInfo['query'])) {
            $path .= ('?' . $urlInfo['query']);
        }
        $headers = [];
        if (!empty($request->headers)) {
            $headers = $request->headers;
        }
        $headers['User-Agent'] = self::userAgent();
        $method = $request->method;
        $client = new Client($host, $port, $ssl);
        $client->setMethod($method);
        $client->setHeaders($headers);
        if (!empty($request->body)) {
            $client->setData($request->body);
        }
        $client->execute($path);
        $client->close();

        $t2 = microtime(true);
        $duration = round($t2 - $t1, 3);

        $statusCode = $client->statusCode;
        if ($statusCode < 0) {
            $error = sprintf('Request error errCode=%s', $client->errCode);
            if ($statusCode === -1) {
                $error = sprintf('Connection timed out errCode=%s', $client->errCode);
            } elseif ($statusCode === -2) {
                $error = 'Request timed out';
            } elseif ($statusCode === -3) {
                $error = 'Connection refused';
            }
            $r = new Response(-1, $duration, [], null, $error);
            return $r;
        }

        $headers = [];
        foreach ($client->headers as $k => $v) {
            $headers[self::ucwordsHyphen($k)] = $v;
        }
        return new Response($statusCode, $duration, $headers, $client->body, null);
    }

    private static function escapeQuotes($str)
    {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }

    private static function ucwordsHyphen($str)
    {
        return str_replace('- ', '-', ucwords(str_replace('-', '- ', $str)));
    }
}
