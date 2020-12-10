<?php

class SerializerTest extends \PHPUnit\Framework\TestCase
{
    //TODO

    public function testBinaryPack()
    {
        require_once __DIR__ . '/../../stubs/grpc-gen/GPBMetadata/Demo.php';
        require_once __DIR__ . '/../../stubs/grpc-gen/Demo/HelloReply.php';

        $grpcReplyMessage = 'hello';
        $grpcReplyData = 'world';

        $grpcReply = \SwFwLess\components\grpc\Serializer::pack(
            (new \Demo\HelloReply())->setMessage($grpcReplyMessage)
                ->setData($grpcReplyData)
        );

        $helloReply = new \Demo\HelloReply();

        \SwFwLess\components\grpc\Serializer::unpack($helloReply, $grpcReply);

        $this->assertEquals(
            $grpcReplyMessage,
            $helloReply->getMessage()
        );
        $this->assertEquals(
            $grpcReplyData,
            $helloReply->getData()
        );
    }
}
