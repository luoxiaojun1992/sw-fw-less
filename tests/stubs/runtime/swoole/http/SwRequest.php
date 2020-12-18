<?php

class SwRequest
{
    public $fd;
    public $streamId;
    public $header;
    public $server;
    public $request;
    public $cookie;
    public $get;
    public $files;
    public $post;
    public $tmpfiles;
    public $rawContent;
    public $data;

    /**
     * @return mixed
     */
    public function rawContent()
    {
        return $this->rawContent;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function __destruct()
    {
        return null;
    }
}
