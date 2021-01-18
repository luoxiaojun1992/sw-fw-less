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
        $response->setProtocolVersion($protocolV2);
        $this->assertEquals(
            $protocolV2,
            $response->getProtocolVersion()
        );
    }

    public function testReasonPhrase()
    {
        $response = $this->createResponse();
        $this->assertEquals(
            \SwFwLess\components\http\Code::phrase(
                \SwFwLess\components\http\Code::STATUS_OK
            ),
            $response->getReasonPhrase()
        );

        $testReasonPhrase = \SwFwLess\components\http\Code::phrase(
            \SwFwLess\components\http\Code::STATUS_NOT_FOUND
        );

        $response->setReasonPhrase($testReasonPhrase);
        $this->assertEquals(
            $testReasonPhrase,
            $response->getReasonPhrase()
        );
    }

    public function testServerError()
    {
        $response = $this->createResponse();
        $this->assertFalse(
            $response->isServerError()
        );

        $response->setStatus(
            \SwFwLess\components\http\Code::STATUS_NOT_FOUND
        );
        $this->assertFalse(
            $response->isServerError()
        );

        $response->setStatus(
            \SwFwLess\components\http\Code::STATUS_BAD_GATEWAY
        );
        $this->assertTrue(
            $response->isServerError()
        );

        $response->setStatus(
            \SwFwLess\components\http\Code::STATUS_FORBIDDEN
        );
        $this->assertFalse(
            $response->isServerError()
        );
    }

    public function testClientError()
    {
        $response = $this->createResponse();
        $this->assertFalse(
            $response->isClientError()
        );

        $response->setStatus(
            \SwFwLess\components\http\Code::STATUS_GATEWAY_TIMEOUT
        );
        $this->assertFalse(
            $response->isClientError()
        );

        $response->setStatus(
            \SwFwLess\components\http\Code::STATUS_UNAUTHORIZED
        );
        $this->assertTrue(
            $response->isClientError()
        );

        $response->setStatus(
            \SwFwLess\components\http\Code::STATUS_INTERNAL_SERVER_ERROR
        );
        $this->assertFalse(
            $response->isClientError()
        );
    }

    public function testOutput()
    {
        $rawResponseContent = 'bar';
        $rawResponse = \SwFwLess\components\http\Response::output($rawResponseContent);
        $this->assertEquals(
            $rawResponseContent,
            $rawResponse->getContent()
        );
    }

    public function testJson()
    {
        $arr = ['foo' => 'bar'];
        $jsonResponse = \SwFwLess\components\http\Response::json($arr);
        $this->assertJsonStringEqualsJsonString(
            json_encode($arr),
            $jsonResponse->getContent()
        );

        $arr = ['foo' => '中文'];
        $jsonResponse = \SwFwLess\components\http\Response::json($arr);
        $expectedResponseContent = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $actualResponseContent = $jsonResponse->getContent();
        $this->assertJsonStringEqualsJsonString(
            $expectedResponseContent,
            $actualResponseContent
        );
        $this->assertEquals(
            $expectedResponseContent,
            $actualResponseContent
        );

        $arr = ['foo' => '中文'];
        $jsonResponse = \SwFwLess\components\http\Response::json(
            $arr,
            200,
            [],
            [],
            0
        );
        $expectedResponseContent = json_encode($arr);
        $actualResponseContent = $jsonResponse->getContent();
        $this->assertJsonStringEqualsJsonString(
            $expectedResponseContent,
            $actualResponseContent
        );
        $this->assertEquals(
            $expectedResponseContent,
            $actualResponseContent
        );
    }

    public function testGrpc()
    {
        //TODO

        $this->assertTrue(true);
    }
}
