<?php

class RouteTest extends \PHPUnit\Framework\TestCase
{
    protected function createDispatcher()
    {

    }

    public function testMiddleware()
    {
        require_once __DIR__ . '/../stubs/middlewares/Route.php';

        $routeMiddleware = new Route();

        //TODO
    }
}
