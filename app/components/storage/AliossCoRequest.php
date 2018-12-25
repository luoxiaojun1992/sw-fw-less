<?php

namespace App\components\storage;

use OSS\Http\RequestCore_Exception;
use OSS\Http\ResponseCore;
use Swoole\Coroutine\Http\Client;

/**
 * Handles all HTTP requests using cURL and manages the responses.
 *
 * @version 2011.06.07
 * @copyright 2006-2011 Ryan Parman
 * @copyright 2006-2010 Foleeo Inc.
 * @copyright 2010-2011 Amazon.com, Inc. or its affiliates.
 * @copyright 2008-2011 Contributors
 * @license http://opensource.org/licenses/bsd-license.php Simplified BSD License
 */
class AliossCoRequest
{
    /**
     * The URL being requested.
     */
    public $request_url;

    /**
     * The headers being sent in the request.
     */
    public $request_headers;

    /**
     * The raw response callback headers
     */
    public $response_raw_headers;

    /**
     * Response body when error occurs
     */
    public $response_error_body;

    /**
     *The hander of write file
     */
    public $write_file_handle;

    /**
     * The body being sent in the request.
     */
    public $request_body;

    /**
     * The response returned by the request.
     */
    public $response;

    /**
     * The headers returned by the request.
     */
    public $response_headers;

    /**
     * The body returned by the request.
     */
    public $response_body;

    /**
     * The HTTP status code returned by the request.
     */
    public $response_code;

    /**
     * Additional response data.
     */
    public $response_info;

    /**
     * The method by which the request is being made.
     */
    public $method;

    /**
     * Stores the proxy settings to use for the request.
     */
    public $proxy = null;

    /**
     * The username to use for the request.
     */
    public $username = null;

    /**
     * The password to use for the request.
     */
    public $password = null;

    /**
     * Custom CURLOPT settings.
     */
    public $curlopts = null;

    /**
     * The state of debug mode.
     */
    public $debug_mode = false;

    /**
     * The default class to use for HTTP Requests (defaults to <RequestCore>).
     */
    public $request_class = 'OSS\Http\RequestCore';

    /**
     * The default class to use for HTTP Responses (defaults to <ResponseCore>).
     */
    public $response_class = 'OSS\Http\ResponseCore';

    /**
     * Default useragent string to use.
     */
    public $useragent = 'RequestCore/1.4.3';

    /**
     * File to read from while streaming up.
     */
    public $read_file = null;

    /**
     * The resource to read from while streaming up.
     */
    public $read_stream = null;

    /**
     * The size of the stream to read from.
     */
    public $read_stream_size = null;

    /**
     * The length already read from the stream.
     */
    public $read_stream_read = 0;

    /**
     * File to write to while streaming down.
     */
    public $write_file = null;

    /**
     * The resource to write to while streaming down.
     */
    public $write_stream = null;

    /**
     * Stores the intended starting seek position.
     */
    public $seek_position = null;

    /**
     * The location of the cacert.pem file to use.
     */
    public $cacert_location = false;

    /**
     * The state of SSL certificate verification.
     */
    public $ssl_verification = true;

    /**
     * The user-defined callback function to call when a stream is read from.
     */
    public $registered_streaming_read_callback = null;

    /**
     * The user-defined callback function to call when a stream is written to.
     */
    public $registered_streaming_write_callback = null;

    /**
     * 请求超时时间， 默认是5184000秒，6天
     *
     * @var int
     */
    public $timeout = 5184000;

    /**
     * 连接超时时间，默认是10秒
     *
     * @var int
     */
    public $connect_timeout = 10;

    /**
     * 请求耗时
     *
     * @var float
     */
    private $duration;

    /*%******************************************************************************************%*/
    // CONSTANTS

    /**
     * GET HTTP Method
     */
    const HTTP_GET = 'GET';

    /**
     * POST HTTP Method
     */
    const HTTP_POST = 'POST';

    /**
     * PUT HTTP Method
     */
    const HTTP_PUT = 'PUT';

    /**
     * DELETE HTTP Method
     */
    const HTTP_DELETE = 'DELETE';

    /**
     * HEAD HTTP Method
     */
    const HTTP_HEAD = 'HEAD';


    /*%******************************************************************************************%*/
    // CONSTRUCTOR/DESTRUCTOR

    /**
     * Constructs a new instance of this class.
     *
     * @param string $url (Optional) The URL to request or service endpoint to query.
     * @param string $proxy (Optional) The faux-url to use for proxy settings. Takes the following format: `proxy://user:pass@hostname:port`
     * @param array $helpers (Optional) An associative array of classnames to use for request, and response functionality. Gets passed in automatically by the calling class.
     * @return $this A reference to the current instance.
     */
    public function __construct($url = null, $proxy = null, $helpers = null)
    {
        // Set some default values.
        $this->request_url = $url;
        $this->method = self::HTTP_GET;
        $this->request_headers = array();
        $this->request_body = '';

        // Set a new Request class if one was set.
        if (isset($helpers['request']) && !empty($helpers['request'])) {
            $this->request_class = $helpers['request'];
        }

        // Set a new Request class if one was set.
        if (isset($helpers['response']) && !empty($helpers['response'])) {
            $this->response_class = $helpers['response'];
        }

        if ($proxy) {
            $this->set_proxy($proxy);
        }

        return $this;
    }

    /**
     * Destructs the instance. Closes opened file handles.
     *
     * @return $this A reference to the current instance.
     */
    public function __destruct()
    {
        if (isset($this->read_file) && isset($this->read_stream)) {
            fclose($this->read_stream);
        }

        if (isset($this->write_file) && isset($this->write_stream)) {
            fclose($this->write_stream);
        }

        return $this;
    }


    /*%******************************************************************************************%*/
    // REQUEST METHODS

    /**
     * Sets the credentials to use for authentication.
     *
     * @param string $user (Required) The username to authenticate with.
     * @param string $pass (Required) The password to authenticate with.
     * @return $this A reference to the current instance.
     */
    public function set_credentials($user, $pass)
    {
        $this->username = $user;
        $this->password = $pass;
        return $this;
    }

    /**
     * Adds a custom HTTP header to the cURL request.
     *
     * @param string $key (Required) The custom HTTP header to set.
     * @param mixed $value (Required) The value to assign to the custom HTTP header.
     * @return $this A reference to the current instance.
     */
    public function add_header($key, $value)
    {
        $this->request_headers[$key] = $value;
        return $this;
    }

    /**
     * Removes an HTTP header from the cURL request.
     *
     * @param string $key (Required) The custom HTTP header to set.
     * @return $this A reference to the current instance.
     */
    public function remove_header($key)
    {
        if (isset($this->request_headers[$key])) {
            unset($this->request_headers[$key]);
        }
        return $this;
    }

    /**
     * Set the method type for the request.
     *
     * @param string $method (Required) One of the following constants: <HTTP_GET>, <HTTP_POST>, <HTTP_PUT>, <HTTP_HEAD>, <HTTP_DELETE>.
     * @return $this A reference to the current instance.
     */
    public function set_method($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Sets a custom useragent string for the class.
     *
     * @param string $ua (Required) The useragent string to use.
     * @return $this A reference to the current instance.
     */
    public function set_useragent($ua)
    {
        $this->useragent = $ua;
        return $this;
    }

    /**
     * Set the body to send in the request.
     *
     * @param string $body (Required) The textual content to send along in the body of the request.
     * @return $this A reference to the current instance.
     */
    public function set_body($body)
    {
        $this->request_body = $body;
        return $this;
    }

    /**
     * Set the URL to make the request to.
     *
     * @param string $url (Required) The URL to make the request to.
     * @return $this A reference to the current instance.
     */
    public function set_request_url($url)
    {
        $this->request_url = $url;
        return $this;
    }

    /**
     * Set additional CURLOPT settings. These will merge with the default settings, and override if
     * there is a duplicate.
     *
     * @param array $curlopts (Optional) A set of key-value pairs that set `CURLOPT` options. These will merge with the existing CURLOPTs, and ones passed here will override the defaults. Keys should be the `CURLOPT_*` constants, not strings.
     * @return $this A reference to the current instance.
     */
    public function set_curlopts($curlopts)
    {
        $this->curlopts = $curlopts;
        return $this;
    }

    /**
     * Sets the length in bytes to read from the stream while streaming up.
     *
     * @param integer $size (Required) The length in bytes to read from the stream.
     * @return $this A reference to the current instance.
     */
    public function set_read_stream_size($size)
    {
        $this->read_stream_size = $size;

        return $this;
    }

    /**
     * Sets the resource to read from while streaming up. Reads the stream from its current position until
     * EOF or `$size` bytes have been read. If `$size` is not given it will be determined by <php:fstat()> and
     * <php:ftell()>.
     *
     * @param resource $resource (Required) The readable resource to read from.
     * @param integer $size (Optional) The size of the stream to read.
     * @return $this A reference to the current instance.
     */
    public function set_read_stream($resource, $size = null)
    {
        if (!isset($size) || $size < 0) {
            $stats = fstat($resource);

            if ($stats && $stats['size'] >= 0) {
                $position = ftell($resource);

                if ($position !== false && $position >= 0) {
                    $size = $stats['size'] - $position;
                }
            }
        }

        $this->read_stream = $resource;

        return $this->set_read_stream_size($size);
    }

    /**
     * Sets the file to read from while streaming up.
     *
     * @param string $location (Required) The readable location to read from.
     * @return $this A reference to the current instance.
     */
    public function set_read_file($location)
    {
        $this->read_file = $location;
        $read_file_handle = fopen($location, 'r');

        return $this->set_read_stream($read_file_handle);
    }

    /**
     * Sets the resource to write to while streaming down.
     *
     * @param resource $resource (Required) The writeable resource to write to.
     * @return $this A reference to the current instance.
     */
    public function set_write_stream($resource)
    {
        $this->write_stream = $resource;

        return $this;
    }

    /**
     * Sets the file to write to while streaming down.
     *
     * @param string $location (Required) The writeable location to write to.
     * @return $this A reference to the current instance.
     */
    public function set_write_file($location)
    {
        $this->write_file = $location;
    }

    /**
     * Set the proxy to use for making requests.
     *
     * @param string $proxy (Required) The faux-url to use for proxy settings. Takes the following format: `proxy://user:pass@hostname:port`
     * @return $this A reference to the current instance.
     */
    public function set_proxy($proxy)
    {
        $proxy = parse_url($proxy);
        $proxy['user'] = isset($proxy['user']) ? $proxy['user'] : null;
        $proxy['pass'] = isset($proxy['pass']) ? $proxy['pass'] : null;
        $proxy['port'] = isset($proxy['port']) ? $proxy['port'] : null;
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * Set the intended starting seek position.
     *
     * @param integer $position (Required) The byte-position of the stream to begin reading from.
     * @return $this A reference to the current instance.
     */
    public function set_seek_position($position)
    {
        $this->seek_position = isset($position) ? (integer)$position : null;

        return $this;
    }

    /**
     * A callback function that is invoked by cURL for streaming up.
     *
     * @param resource $curl_handle (Required) The cURL handle for the request.
     * @param resource $header_content (Required) The header callback result.
     * @return headers from a stream.
     */
    public function streaming_header_callback($curl_handle, $header_content)
    {
        $code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        if (isset($this->write_file) && intval($code) / 100 == 2 && !isset($this->write_file_handle))
        {
            $this->write_file_handle = fopen($this->write_file, 'w');
            $this->set_write_stream($this->write_file_handle);
        }

        $this->response_raw_headers .= $header_content;
        return strlen($header_content);
    }


    /**
     * Register a callback function to execute whenever a data stream is read from using
     * <CFRequest::streaming_read_callback()>.
     *
     * The user-defined callback function should accept three arguments:
     *
     * <ul>
     *    <li><code>$curl_handle</code> - <code>resource</code> - Required - The cURL handle resource that represents the in-progress transfer.</li>
     *    <li><code>$file_handle</code> - <code>resource</code> - Required - The file handle resource that represents the file on the local file system.</li>
     *    <li><code>$length</code> - <code>integer</code> - Required - The length in kilobytes of the data chunk that was transferred.</li>
     * </ul>
     *
     * @param string|array|function $callback (Required) The callback function is called by <php:call_user_func()>, so you can pass the following values: <ul>
     *    <li>The name of a global function to execute, passed as a string.</li>
     *    <li>A method to execute, passed as <code>array('ClassName', 'MethodName')</code>.</li>
     *    <li>An anonymous function (PHP 5.3+).</li></ul>
     * @return $this A reference to the current instance.
     */
    public function register_streaming_read_callback($callback)
    {
        $this->registered_streaming_read_callback = $callback;

        return $this;
    }

    /**
     * Register a callback function to execute whenever a data stream is written to using
     * <CFRequest::streaming_write_callback()>.
     *
     * The user-defined callback function should accept two arguments:
     *
     * <ul>
     *    <li><code>$curl_handle</code> - <code>resource</code> - Required - The cURL handle resource that represents the in-progress transfer.</li>
     *    <li><code>$length</code> - <code>integer</code> - Required - The length in kilobytes of the data chunk that was transferred.</li>
     * </ul>
     *
     * @param string|array|function $callback (Required) The callback function is called by <php:call_user_func()>, so you can pass the following values: <ul>
     *    <li>The name of a global function to execute, passed as a string.</li>
     *    <li>A method to execute, passed as <code>array('ClassName', 'MethodName')</code>.</li>
     *    <li>An anonymous function (PHP 5.3+).</li></ul>
     * @return $this A reference to the current instance.
     */
    public function register_streaming_write_callback($callback)
    {
        $this->registered_streaming_write_callback = $callback;

        return $this;
    }


    /*%******************************************************************************************%*/
    // PREPARE, SEND, AND PROCESS REQUEST

    /**
     * A callback function that is invoked by cURL for streaming up.
     *
     * @param resource $curl_handle (Required) The cURL handle for the request.
     * @param resource $file_handle (Required) The open file handle resource.
     * @param integer $length (Required) The maximum number of bytes to read.
     * @return binary Binary data from a stream.
     */
    public function streaming_read_callback($curl_handle, $file_handle, $length)
    {
        // Once we've sent as much as we're supposed to send...
        if ($this->read_stream_read >= $this->read_stream_size) {
            // Send EOF
            return '';
        }

        // If we're at the beginning of an upload and need to seek...
        if ($this->read_stream_read == 0 && isset($this->seek_position) && $this->seek_position !== ftell($this->read_stream)) {
            if (fseek($this->read_stream, $this->seek_position) !== 0) {
                throw new RequestCore_Exception('The stream does not support seeking and is either not at the requested position or the position is unknown.');
            }
        }

        $read = fread($this->read_stream, min($this->read_stream_size - $this->read_stream_read, $length)); // Remaining upload data or cURL's requested chunk size
        $this->read_stream_read += strlen($read);

        $out = $read === false ? '' : $read;

        // Execute callback function
        if ($this->registered_streaming_read_callback) {
            call_user_func($this->registered_streaming_read_callback, $curl_handle, $file_handle, $out);
        }

        return $out;
    }

    /**
     * A callback function that is invoked by cURL for streaming down.
     *
     * @param resource $curl_handle (Required) The cURL handle for the request.
     * @param binary $data (Required) The data to write.
     * @return integer The number of bytes written.
     */
    public function streaming_write_callback($curl_handle, $data)
    {
        $code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        if (intval($code) / 100 != 2)
        {
            $this->response_error_body .= $data;
            return strlen($data);
        }

        $length = strlen($data);
        $written_total = 0;
        $written_last = 0;

        while ($written_total < $length) {
            $written_last = fwrite($this->write_stream, substr($data, $written_total));

            if ($written_last === false) {
                return $written_total;
            }

            $written_total += $written_last;
        }

        // Execute callback function
        if ($this->registered_streaming_write_callback) {
            call_user_func($this->registered_streaming_write_callback, $curl_handle, $written_total);
        }

        return $written_total;
    }

    /**
     * @return Client
     */
    public function prep_request()
    {
        $urlInfo = parse_url($this->request_url);
        $scheme = isset($urlInfo['scheme']) ? $urlInfo['scheme'] : 'http';
        $ssl = 'https' === $scheme;
        $host = $urlInfo['host'];
        if (!isset($urlInfo['port'])) {
            $port = $ssl ? 443 : 80;
        } else {
            $port = $urlInfo['port'];
        }

        $headers = [];
        if (!empty($this->request_headers)) {
            $headers = $this->request_headers;
        }
        $headers['Referer'] = $this->request_url;
        $headers['User-Agent'] = $this->useragent;
        // Set credentials for HTTP Basic/Digest Authentication.
        if ($this->username && $this->password) {
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($this->username . ':' . $this->password));
        }

        $client = new Client($host, $port, $ssl);
        $client->setMethod($this->method);
        $client->setHeaders($headers);
        $client->set(['timeout' => max($this->timeout, $this->connect_timeout)]);
        if (!empty($this->request_body)) {
            $client->setData($this->request_body);
        }

        return $client;
    }

    /**
     * @param null|Client $client
     * @param null $response
     * @return bool|ResponseCore
     */
    public function process_response($client = null, $response = null)
    {
        // Accept a custom one if it's passed.
        if ($client && $response) {
            $this->response = $response;
        }

        // As long as this came back as a valid resource...
        if ($client instanceof Client) {
            foreach ($client->headers as $k => $v) {
                $this->response_headers[strtolower($k)] = $v;
            }
            $this->response_body = $client->body;
            $this->response_code = $client->statusCode;
            $this->response_info = [
                'url' => $this->request_url,
                'content_type' => isset($this->response_headers['content-type']) ? $this->response_headers['content-type'] : '',
                'http_code' => $this->response_code,
                'header_size' => 1,
                'request_size' => strlen($client->requestBody),
                'filetime' => 0,
                'ssl_verify_result' => false,
                'redirect_count' => 0,
                'total_time' => $this->duration,
                'namelookup_time' => $this->duration,
                'connect_time' => $this->duration,
                'pretransfer_time' => $this->duration,
                'size_upload' => 0,
                'size_download' => 0,
                'speed_download' => 0,
                'speed_upload' => 0,
                'download_content_length' => 0,
                'upload_content_length' => 0,
                'starttransfer_time',
                'redirect_time' => 0,
                'certinfo' => null,
                'primary_ip' => $client->host,
                'primary_port' => $client->port,
                'local_ip' => gethostname(),
                'local_port' => 0,
                'redirect_url' => '',
                'request_header' => $client->requestHeaders,
            ];

            $this->response_headers['info'] = $this->response_info;
            $this->response_headers['info']['method'] = $this->method;

            if ($client && $response) {
                return new ResponseCore($this->response_headers, $this->response_body, $this->response_code);
            }
        }

        // Return false
        return false;
    }

    /**
     * @param bool $parse
     * @return bool|ResponseCore
     * @throws RequestCore_Exception
     */
    public function send_request($parse = false)
    {
        set_time_limit(0);

        $t1 = microtime(true);
        $client = $this->prep_request();

        $urlInfo = parse_url($this->request_url);
        $path = isset($urlInfo['path']) ? $urlInfo['path'] : '/';
        if (!empty($urlInfo['query'])) {
            $path .= ('?' . $urlInfo['query']);
        }

        $client->execute($path);
        $client->close();
        $t2 = microtime(true);
        $this->duration = round($t2 - $t1, 3);

        $this->response = $client->body;

        if ($client->statusCode < 0) {
            throw new RequestCore_Exception('Client error: ' . $client->errCode);
        }

        $parsed_response = $this->process_response($client, $this->response);

        if ($parse) {
            return $parsed_response;
        }

        return $this->response;
    }

    /*%******************************************************************************************%*/
    // RESPONSE METHODS

    /**
     * Get the HTTP response headers from the request.
     *
     * @param string $header (Optional) A specific header value to return. Defaults to all headers.
     * @return string|array All or selected header values.
     */
    public function get_response_header($header = null)
    {
        if ($header) {
            return $this->response_headers[strtolower($header)];
        }
        return $this->response_headers;
    }

    /**
     * Get the HTTP response body from the request.
     *
     * @return string The response body.
     */
    public function get_response_body()
    {
        return $this->response_body;
    }

    /**
     * Get the HTTP response code from the request.
     *
     * @return string The HTTP response code.
     */
    public function get_response_code()
    {
        return $this->response_code;
    }
}
