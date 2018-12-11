<?php

namespace App\components;

class Response
{
    private $content;
    private $status = 200;
    private $headers = [];

    /**
     * @param mixed $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param mixed $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $content
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function output($content, $status = 200, $headers = [])
    {
        return (new self)->setContent($content)->setStatus($status)->setHeaders($headers);
    }

    /**
     * @param $arr
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function json($arr, $status = 200, $headers = [])
    {
        $content = is_array($arr) ? json_encode($arr, JSON_UNESCAPED_UNICODE) : $arr;
        return self::output($content, $status, $headers);
    }
}
