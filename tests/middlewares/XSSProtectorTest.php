<?php

namespace SwFwLessTest\middlewares;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\Config;
use SwFwLess\components\http\Request;
use SwFwLess\components\pool\ObjectPool;
use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;
use SwFwLess\middlewares\AbstractMiddleware;
use SwFwLess\middlewares\XSSProtector;

class XSSProtectorTest extends TestCase
{
    protected function beforeTest()
    {
        parent::setUp();
        Config::initByArr([
            'di_switch' => false,
            'pool' => [
                'switch' => 1,
                'objects' => [],
            ],

        ]);
        ObjectPool::create(Config::get('pool'));
    }

    protected function afterTest()
    {
        parent::tearDown();
        Config::clear();
        ObjectPool::clearInstance();
    }

    /**
     * @return \SwRequest
     */
    private function createSwRequest()
    {
        require_once __DIR__ . '/../stubs/runtime/swoole/http/SwRequest.php';
        return new \SwRequest();
    }

    /**
     * @param null $swRequest
     * @return \SwFwLess\components\http\Request
     */
    private function createSwfRequest($swRequest = null)
    {
        require_once __DIR__ . '/../stubs/components/http/Request.php';
        return \Request::fromSwRequest($swRequest ?? $this->createSwRequest());
    }

    public function testMiddleware()
    {
        $this->beforeTest();

        $swRequest = $this->createSwRequest();
        $swfRequest = $this->createSwfRequest($swRequest);

        $xssProtectorMiddleware = new XSSProtector();
        $xssProtectorMiddleware->setParameters([$swfRequest]);
        $xssProtectorMiddleware->setNext(
            (new Class() extends AbstractMiddleware {
                public function handle(Request $request)
                {
                    return $request->all();
                }
            })->setParameters([$swfRequest])
        );

        $this->assertEquals('[]', $xssProtectorMiddleware->call()->getContent());

        $swRequest = $this->createSwRequest();
        $swRequest->get = [
            MetasyntacticVars::FOO => MetasyntacticVars::FOO . '<script></script>' . MetasyntacticVars::QUUX,
            MetasyntacticVars::BAR => MetasyntacticVars::BAR,
        ];
        $swRequest->post = [
            MetasyntacticVars::BAZ => MetasyntacticVars::BAZ,
            MetasyntacticVars::QUX => MetasyntacticVars::QUX,
        ];
        $swfRequest = $this->createSwfRequest($swRequest);

        $xssProtectorMiddleware = new XSSProtector();
        $xssProtectorMiddleware->setParameters([$swfRequest]);
        $xssProtectorMiddleware->setNext(
            (new Class() extends AbstractMiddleware {
                public function handle(Request $request)
                {
                    return $request->all();
                }
            })->setParameters([$swfRequest])
        );

        $this->assertEquals(
            json_encode(
                [
                    MetasyntacticVars::FOO => MetasyntacticVars::FOO . MetasyntacticVars::QUUX,
                    MetasyntacticVars::BAR => MetasyntacticVars::BAR,
                    MetasyntacticVars::BAZ => MetasyntacticVars::BAZ,
                    MetasyntacticVars::QUX => MetasyntacticVars::QUX,
                ]
            ),
            $xssProtectorMiddleware->call()->getContent()
        );

        $this->afterTest();
    }
}
