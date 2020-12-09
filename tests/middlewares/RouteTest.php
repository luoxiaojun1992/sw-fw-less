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
     * @throws Throwable
     */
    public function testMiddleware()
    {
        require_once __DIR__ . '/../stubs/middlewares/Route.php';

        $routeMiddleware = new Route();

        require_once __DIR__ . '/../stubs/components/http/Request.php';

        $request = (new Request())->setUri('/test')
            ->setMethod('GET');

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
