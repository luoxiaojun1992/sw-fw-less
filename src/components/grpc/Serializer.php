<?php

namespace SwFwLess\components\grpc;

class Serializer
{
    /**
     * @param \Google\Protobuf\Internal\Message $message
     * @param $body
     * @param bool $hasHeader
     * @param bool $isJson
     */
    public static function unpack(\Google\Protobuf\Internal\Message $message, $body, $hasHeader = true, $isJson = false)
    {
        if ($hasHeader) {
            $body = substr($body, 5);
        }

        if ($isJson) {
            $message->mergeFromJsonString($body);
        } else {
            $message->mergeFromString($body);
        }
    }

    /**
     * @param $body
     * @return array
     */
    public static function unpackHeader($body)
    {
        return unpack('Cflag/Nlength', substr($body, 0, 5));
    }

    /**
     * @param \Google\Protobuf\Internal\Message $message
     * @param bool $isJson
     * @return string
     */
    public static function pack(\Google\Protobuf\Internal\Message $message, $isJson = false)
    {
        if ($isJson) {
            $message = $message->serializeToJsonString();
        } else {
            $message = $message->serializeToString();
        }

        return pack('CN', 0, strlen($message)) . $message;
    }
}
