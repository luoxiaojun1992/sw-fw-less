<?php

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return \SwFwLess\components\http\Response
     */
    protected function createResponse()
    {
        return new \SwFwLess\components\http\Response();
    }

    public function testContent()
    {
        $responseContent = 'pong';
        $response = $this->createResponse();
        $response->setContent($responseContent);
        $this->assertEquals(
            $responseContent,
            $response->getContent()
        );

        $arr = ['foo' => 'bar'];
        $jsonResponse = \SwFwLess\components\http\Response::json($arr);
        $this->assertJsonStringEqualsJsonString(
            json_encode($arr),
            $jsonResponse->getContent()
        );

        $rawResponseContent = 'bar';
        $rawResponse = \SwFwLess\components\http\Response::output($rawResponseContent);
        $this->assertEquals(
            $rawResponseContent,
            $rawResponse->getContent()
        );
    }

    public function testStatus()
    {
        $response = $this->createResponse();
        $responseStatus = $response->getStatus();
        $this->assertEquals(\SwFwLess\components\http\Code::STATUS_OK, $responseStatus);
        $this->assertEquals(
            \SwFwLess\components\http\Code::phrase($responseStatus),
            $response->getReasonPhrase()
        );

        $statusCreated = \SwFwLess\components\http\Code::STATUS_CREATED;
        $response = $this->createResponse();
        $response->setStatus($statusCreated);
        $responseStatus = $response->getStatus();
        $this->assertEquals($statusCreated, $responseStatus);
        $this->assertEquals(
            \SwFwLess\components\http\Code::phrase($responseStatus),
            $response->getReasonPhrase()
        );
    }

    public function testHeaders()
    {
        $response = $this->createResponse();
        $response->setHeaders([
            'content-type' => 'application/json'
        ]);
        $headers = $response->getHeaders();
        $this->assertEquals(
            'application/json',
            $headers['content-type']
        );

        $response = $this->createResponse();
        $response->setHeaders([
            'content-type' => 'application/json',
            'cache-control' => 'no-cache',
        ]);
        $headers = $response->getHeaders();
        $this->assertEquals(
            'application/json',
            $headers['content-type']
        );
        $this->assertEquals(
            'no-cache',
            $headers['cache-control']
        );
    }

    public function testTrailers()
    {
        $response = $this->createResponse();
        $response->setTrailers([
            'grpc-status' => \SwFwLess\components\grpc\Status::OK,
        ]);
        $trailers = $response->getTrailers();
        $this->assertEquals(
            \SwFwLess\components\grpc\Status::OK,
            $trailers['grpc-status']
        );

        $response = $this->createResponse();
        $response->setTrailers([
            'grpc-status' => \SwFwLess\components\grpc\Status::OK,
            'grpc-message' => \SwFwLess\components\grpc\Status::msg(
                \SwFwLess\components\grpc\Status::OK
            )
        ]);
        $trailers = $response->getTrailers();
        $this->assertEquals(
            \SwFwLess\components\grpc\Status::OK,
            $trailers['grpc-status']
        );
        $this->assertEquals(
            \SwFwLess\components\grpc\Status::msg(
                \SwFwLess\components\grpc\Status::OK
            ),
            $trailers['grpc-message']
        );
    }

    public function testProtocolVersion()
    {
        $response = $this->createResponse();
        $this->assertEquals(
            \SwFwLess\components\http\Protocol::HTTP_V1_1,
            $response->getProtocolVersion()
        );

        $protocolV2 = \SwFwLess\components\http\Protocol::HTTP_V2;
        $response = $this->createResponse();
        $response->setProtocolVersion($protocolV2);
        $this->assertEquals(
            $protocolV2,
            $response->getProtocolVersion()
        );
    }
}
