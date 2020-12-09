<?php

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    //TODO

    /**
     * @return \SwFwLess\components\http\Response
     */
    protected function createResponse()
    {
        return new \SwFwLess\components\http\Response();
    }

    public function testStatus()
    {
        $response = $this->createResponse();
        $response->setStatus(200);
        $this->assertEquals(200, $response->getStatus());
    }
}
