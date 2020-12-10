<?php

class SerializerTest extends \PHPUnit\Framework\TestCase
{
    //TODO

    public function testBinaryPack()
    {
        require_once __DIR__ . '/../../stubs/grpc-gen/GPBMetadata/Demo.php';
        require_once __DIR__ . '/../../stubs/grpc-gen/Demo/HelloReply.php';

        $grpcMessageMessage = 'hello';
        $grpcMessageData = 'world';

        $grpcReply = \SwFwLess\components\grpc\Serializer::pack(
            (new \Demo\HelloReply())->setMessage($grpcMessageMessage)
                ->setData($grpcMessageData)
        );

        $grpcMessage = new \Demo\HelloReply();

        \SwFwLess\components\grpc\Serializer::unpack($grpcMessage, $grpcReply);

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
    }
}
