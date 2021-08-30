<?php

namespace SwFwLessTest\components\router;

use Mockery as M;
use PHPUnit\Framework\TestCase;
use SwFwLess\components\Config;
use SwFwLess\components\Helper;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\router\Router;
use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;
use SwFwLess\components\utils\http\Url;
use SwFwLessTest\stubs\services\TestService;

class RouterTest extends TestCase
{
    protected function beforeTest()
    {
        parent::setUp();
        $this->mockSwooleScheduler();
        Config::initByArr([
            'pool' => [
                'switch' => 1,
                'objects' => [],
            ]
        ]);
        ObjectPool::create(Config::get('pool'));
    }

    protected function afterTest()
    {
        parent::tearDown();
        Url::clearDecodedCache();
        Router::clearRouteCache();
        Config::clear();
        ObjectPool::clearInstance();
    }

    protected function mockSwooleScheduler()
    {
        $mockScheduler = M::mock('alias:' . 'SwFwLess\components\swoole\Scheduler');
        $mockScheduler->shouldReceive('withoutPreemptive')
            ->with(M::type('callable'))
            ->andReturnUsing(function ($arg) {
                return call_user_func($arg);
            });
    }

    /**
     * @return SwRequest
     */
    private function createSwRequest()
    {
        require_once __DIR__ . '/../../stubs/runtime/swoole/http/SwRequest.php';
        return new \SwRequest();
    }

    /**
     * @param null $swRequest
     * @return \SwFwLess\components\http\Request
     */
    private function createSwfRequest($swRequest = null)
    {
        require_once __DIR__ . '/../../stubs/components/http/Request.php';
        return \Request::fromSwRequest($swRequest ?? $this->createSwRequest());
    }

    private function createTestService()
    {
        require_once __DIR__ . '/../../stubs/services/TestService.php';
    }

    public function testParseRouteInfo()
    {
        $this->beforeTest();

        $swRequest = $this->createSwRequest();
        $swRequest->server = [
            'request_method' => 'GET',
            'request_uri' => '/test'
        ];
        $this->createTestService();
        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute(
                'GET',
                '/test',
                ['/test', TestService::class, 'test']
            );
        });
        $swfRequest = $this->createSwfRequest($swRequest);
        $router = Router::create($swfRequest, $dispatcher);
        $router->parseRouteInfo();
        $this->assertEquals(
            Helper::jsonEncode([MetasyntacticVars::FOO => MetasyntacticVars::BAR]),
            $router->createController()->call()->getContent()
        );

        $this->afterTest();
    }
}
