<?php

class RouteTest extends \PHPUnit\Framework\TestCase
{
    protected function createDispatcher()
    {
        require_once __DIR__ . '/../stubs/middlewares/TestService.php';

        return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute(
                'GET',
                '/test',
                ['/test', TestService::class, 'ping']
            );
        });
    }

    /**
     * @return SwRequest
     */
    private function createSwRequest()
    {
        require_once __DIR__ . '/../stubs/runtime/swoole/http/SwRequest.php';
        return new SwRequest();
    }

    /**
     * @param null $swRequest
     * @return \SwFwLess\components\http\Request
     */
    private function createSwfRequest($swRequest = null)
    {
        require_once __DIR__ . '/../stubs/components/http/Request.php';
        return Request::fromSwRequest($swRequest ?? $this->createSwRequest());
    }

    /**
     * @throws Throwable
     */
    public function testMiddleware()
    {
        require_once __DIR__ . '/../stubs/middlewares/Route.php';

        $routeMiddleware = new Route();

        $swRequest = $this->createSwRequest();
        $swRequest->server = [];
        $swRequest->server['request_uri'] = '/test';
        $swRequest->server['request_method'] = 'GET';

        $request = $this->createSwfRequest($swRequest);

        $routeMiddleware->setParametersAndOptions(
            [$request],
            $this->createDispatcher()
        );

        /** @var \SwFwLess\components\http\Response $response */
        $response = $routeMiddleware->call();

        $this->assertEquals(
            'pong',
            $response->getContent()
        );
    }
}
