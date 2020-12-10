<?php

class SerializerTest extends \PHPUnit\Framework\TestCase
{
    public function testBinaryPack()
    {
        require_once __DIR__ . '/../../stubs/grpc-gen/GPBMetadata/Demo.php';
        require_once __DIR__ . '/../../stubs/grpc-gen/Demo/HelloReply.php';

        $grpcMessageMessage = 'hello';
        $grpcMessageData = 'world';

        $grpcReply = \SwFwLess\components\grpc\Serializer::pack(
            (new \Demo\HelloReply())->setMessage($grpcMessageMessage)
                ->setData($grpcMessageData),
            false
        );

        $grpcMessage = new \Demo\HelloReply();

        \SwFwLess\components\grpc\Serializer::unpack($grpcMessage, $grpcReply, true, false);

        $this->assertEquals(
            $grpcMessageMessage,
            $grpcMessage->getMessage()
        );
        $this->assertEquals(
            $grpcMessageData,
            $grpcMessage->getData()
        );

        [$grpcReplyHeader, $grpcReplyMessage] = \SwFwLess\components\grpc\Serializer::extractHeaderMessage(
            $grpcReply
        );

        $grpcHeader = \SwFwLess\components\grpc\Serializer::unpackHeader($grpcReplyHeader, false);
        $this->assertEquals(
            0,
            $grpcHeader['flag']
        );
        $this->assertEquals(
            strlen($grpcReplyMessage),
            $grpcHeader['length']
        );

        $grpcMessage = new \Demo\HelloReply();
        \SwFwLess\components\grpc\Serializer::unpack($grpcMessage, $grpcReplyMessage, false, false);
        $this->assertEquals(
            $grpcMessageMessage,
            $grpcMessage->getMessage()
        );
        $this->assertEquals(
            $grpcMessageData,
            $grpcMessage->getData()
        );
    }

    public function testJsonPack()
    {
        require_once __DIR__ . '/../../stubs/grpc-gen/GPBMetadata/Demo.php';
        require_once __DIR__ . '/../../stubs/grpc-gen/Demo/HelloReply.php';

        $grpcMessageMessage = 'hello';
        $grpcMessageData = 'world';

        $grpcReply = \SwFwLess\components\grpc\Serializer::pack(
            (new \Demo\HelloReply())->setMessage($grpcMessageMessage)
                ->setData($grpcMessageData),
            true
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => $grpcMessageMessage, 'data' => $grpcMessageData]),
            \SwFwLess\components\grpc\Serializer::extractHeaderMessage($grpcReply)[1]
        );

        $grpcMessage = new \Demo\HelloReply();

        \SwFwLess\components\grpc\Serializer::unpack($grpcMessage, $grpcReply, true, true);

        $this->assertEquals(
            $grpcMessageMessage,
            $grpcMessage->getMessage()
        );
        $this->assertEquals(
            $grpcMessageData,
            $grpcMessage->getData()
        );

        [$grpcReplyHeader, $grpcReplyMessage] = \SwFwLess\components\grpc\Serializer::extractHeaderMessage(
            $grpcReply
        );

        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => $grpcMessageMessage, 'data' => $grpcMessageData]),
            $grpcReplyMessage
        );

        $grpcHeader = \SwFwLess\components\grpc\Serializer::unpackHeader($grpcReplyHeader, false);
        $this->assertEquals(
            0,
            $grpcHeader['flag']
        );
        $this->assertEquals(
            strlen($grpcReplyMessage),
            $grpcHeader['length']
        );

        $grpcMessage = new \Demo\HelloReply();
        \SwFwLess\components\grpc\Serializer::unpack($grpcMessage, $grpcReplyMessage, false, true);
        $this->assertEquals(
            $grpcMessageMessage,
            $grpcMessage->getMessage()
        );
        $this->assertEquals(
            $grpcMessageData,
            $grpcMessage->getData()
        );
    }
}
