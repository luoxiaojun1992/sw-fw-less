<?php

class RequestTest extends \PHPUnit\Framework\TestCase
{
    private function assertMultiEquals($expected, $actualValues)
    {
        array_map(function ($actual) use ($expected) {
            $this->assertEquals($expected, $actual);
        }, $actualValues);
    }

    /**
     * @return \Swoole\Http\Request
     */
    private function createSwRequest()
    {
        return new \Swoole\Http\Request();
    }

    /**
     * @param null $swRequest
     * @return \SwFwLess\components\http\Request
     */
    private function createSwfRequest($swRequest = null)
    {
        return \SwFwLess\components\http\Request::fromSwRequest($swRequest ?? $this->createSwRequest());
    }

    public function testRoute()
    {
        $swfRequest = $this->createSwfRequest();

        $this->assertEquals('/test', $swfRequest->setRoute('/test')->getRoute());

        $swfRequest = $this->createSwfRequest();

        $this->assertEquals(null, $swfRequest->getRoute());
    }

    /**
     * @param $paramType
     */
    private function tParam($paramType)
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->{$paramType}['foo'] = 'bar';
        $swRequest->{$paramType}['FOO'] = 'BAR';

        $this->assertEquals('bar', $swfRequest->{$paramType}('foo'));
        $this->assertEquals('BAR', $swfRequest->{$paramType}('FOO'));

        $swfRequest = $this->createSwfRequest();

        $this->assertEquals(null, $swfRequest->{$paramType}('foo'));

        $swfRequest = $this->createSwfRequest();

        $this->assertEquals('default', $swfRequest->{$paramType}('foo', 'default'));
    }

    public function testGet()
    {
        $this->tParam('get');
    }

    public function testPost()
    {
        $this->tParam('post');
    }

    public function testFile()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->files['foo'] = 'bar';
        $swRequest->files['FOO'] = 'BAR';

        $this->assertEquals('bar', $swfRequest->file('foo'));
        $this->assertEquals('BAR', $swfRequest->file('FOO'));

        $swfRequest = $this->createSwfRequest();

        $this->assertEquals(null, $swfRequest->file('foo'));
    }

    public function testParam()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->post['foo'] = 'bar';

        $this->assertEquals('bar', $swfRequest->param('foo'));

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->get['foo'] = 'bar';

        $this->assertEquals('bar', $swfRequest->param('foo'));

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->files['foo'] = 'bar';

        $this->assertEquals('bar', $swfRequest->param('foo'));

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $this->assertEquals('bar', $swfRequest->param('foo', 'bar'));
    }

    public function testAll()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->get['param1'] = 'value1';
        $swRequest->post['param2'] = 'value2';
        $swRequest->files['param3'] = 'value3';

        $this->assertEquals(['param1' => 'value1', 'param2' => 'value2', 'param3' => 'value3'], $swfRequest->all());
    }

    public function testHeader()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->header['content-type'] = 'application/json';

        $this->assertMultiEquals('application/json', [
            $swfRequest->header('content-type'),
            $swfRequest->header('Content-Type'),
        ]);

        $this->assertEquals(null, $swfRequest->header('x-foo'));
        $this->assertEquals('bar', $swfRequest->header('x-foo', 'bar'));
    }

    public function testHasHeader()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->header['content-type'] = 'application/json';

        $this->assertMultiEquals(true, [
            $swfRequest->hasHeader('content-type'),
            $swfRequest->hasHeader('Content-Type'),
        ]);

        $this->assertEquals(false, $swfRequest->hasHeader('x-foo'));
    }

    public function testRealIp()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->header['x-real-ip'] = '172.17.0.0';

        $this->assertEquals('172.17.0.0', $swfRequest->realIp());

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->header['x-forwarded-for'] = '172.17.0.0';

        $this->assertEquals('172.17.0.0', $swfRequest->realIp());

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->server['remote_addr'] = '172.17.0.0';

        $this->assertEquals('172.17.0.0', $swfRequest->realIp());

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->header['x-real-ip'] = '172.17.0.0';
        $swRequest->header['x-forwarded-for'] = '10.0.0.0';
        $swRequest->server['remote_addr'] = '10.0.0.0';

        $this->assertEquals('172.17.0.0', $swfRequest->realIp());

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->header['x-forwarded-for'] = '172.17.0.0';
        $swRequest->server['remote_addr'] = '10.0.0.0';

        $this->assertEquals('172.17.0.0', $swfRequest->realIp());

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $this->assertEquals(null, $swfRequest->realIp());
    }

    public function testServer()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->server['remote_addr'] = '10.0.0.0';

        $this->assertMultiEquals('10.0.0.0', [
            $swfRequest->server('remote_addr'),
            $swfRequest->server('REMOTE_ADDR'),
        ]);

        $this->assertEquals(null, $swfRequest->server('server_protocol'));
        $this->assertEquals('bar', $swfRequest->server('server_protocol', 'bar'));
    }

    public function testHasServer()
    {
        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $swRequest->server['remote_addr'] = '10.0.0.0';

        $this->assertMultiEquals(true, [
            $swfRequest->hasServer('remote_addr'),
            $swfRequest->hasServer('REMOTE_ADDR'),
        ]);

        $this->assertEquals(false, $swfRequest->hasServer('server_protocol'));
    }
}
