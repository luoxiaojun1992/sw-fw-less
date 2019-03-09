<?php

namespace App\components\http;

use function Zend\Diactoros\marshalHeadersFromSapi;
use function Zend\Diactoros\marshalMethodFromSapi;
use function Zend\Diactoros\marshalProtocolVersionFromSapi;
use function Zend\Diactoros\marshalUriFromSapi;
use function Zend\Diactoros\normalizeUploadedFiles;
use function Zend\Diactoros\parseCookieHeader;
use Zend\Diactoros\ServerRequest;

class ServerRequestFactory extends \Zend\Diactoros\ServerRequestFactory
{
    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * The ServerRequest created is then passed to the fromServer() method in
     * order to marshal the request URI and headers.
     *
     * @see fromServer()
     * @param array $server
     * @param array $query
     * @param array $body
     * @param array $cookies
     * @param array $files
     * @param array $headers
     * @param string $rawBody
     * @return ServerRequest
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null,
        array $headers = null,
        string $rawBody = null
    ) : ServerRequest {
        if ($server) {
            $formattedServer = [];
            foreach ($server as $key => $value) {
                $formattedServer[strtoupper($key)] = $value;
            }
        } else {
            $formattedServer = [];
        }

        if ($headers) {
            foreach ($headers as $key => $value) {
                $formattedServer['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
            }
        }

        $files   = normalizeUploadedFiles($files);
        $headers = marshalHeadersFromSapi($formattedServer);

        if (null === $cookies && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }

        return new ServerRequest(
            $formattedServer,
            $files,
            marshalUriFromSapi($formattedServer, $headers),
            marshalMethodFromSapi($formattedServer),
            'data://text/plain,' . (string) $rawBody,
            $headers,
            $cookies,
            $query,
            $body,
            marshalProtocolVersionFromSapi($formattedServer)
        );
    }
}
