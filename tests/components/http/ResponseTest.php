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
        $response->setStatus(200);
        $this->assertEquals(200, $response->getStatus());
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
}
