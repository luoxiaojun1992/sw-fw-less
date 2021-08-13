<?php

namespace SwFwLessTests\components\volcanoo\serializer\json;

use SwFwLess\components\Helper;
use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;
use SwFwLess\components\volcano\Executor;
use SwFwLess\components\volcano\http\extractor\ResponseExtractor;
use SwFwLess\components\volcano\serializer\json\Decoder;
use SwFwLessTest\stubs\components\http\psr\PsrResponse;
use SwFwLessTest\stubs\components\http\psr\PsrStream;
use SwFwLessTest\stubs\components\volcanoo\http\HttpRequest;

class DecoderTest extends \PHPUnit\Framework\TestCase
{
    public function getHttpRequest()
    {
        require_once __DIR__ . '/../../../../stubs/components/volcano/http/HttpRequest.php';
        return HttpRequest::create();
    }

    public function getPsrResponse()
    {
        require_once __DIR__ . '/../../../../stubs/components/http/psr/PsrResponse.php';
        return new PsrResponse();
    }

    public function getPsrStream()
    {
        require_once __DIR__ . '/../../../../stubs/components/http/psr/PsrStream.php';
        return new PsrStream();
    }

    public function testNext()
    {
        $httpRequest = $this->getHttpRequest();

        for ($i = 0; $i < 3; ++$i) {
            $psrStream = $this->getPsrStream();
            $psrStream->write(Helper::jsonEncode([MetasyntacticVars::FOO => $i]));
            $psrResponse = $this->getPsrResponse();
            $psrResponse->withBody($psrStream);
            $httpRequest->addMockResponse($psrResponse);
        }

        $executor = Executor::create()->setPlan(
            Decoder::create()->setNext(
                ResponseExtractor::create()->setNext($httpRequest)
            )
        );

        $this->assertEquals(
            [
                'class' => Decoder::class,
                'info' => [],
                'sub_operator' => [
                    'class' => ResponseExtractor::class,
                    'info' => [],
                    'sub_operator' => [
                        'class' => HttpRequest::class,
                        'info' => [
                            'pre_request' => false,
                            'requests' => [],
                            'request_count' => 3,
                            'mock_response' => [
                                Helper::jsonEncode([MetasyntacticVars::FOO => 0]),
                                Helper::jsonEncode([MetasyntacticVars::FOO => 1]),
                                Helper::jsonEncode([MetasyntacticVars::FOO => 2]),
                            ],
                        ],
                        'sub_operator' => null,
                    ]
                ]
            ],
            $executor->explain()
        );

        $this->assertEquals(
            [
                'class' => Decoder::class,
                'info' => [],
                'sub_operator' => [
                    'class' => ResponseExtractor::class,
                    'info' => [],
                    'sub_operator' => [
                        'class' => HttpRequest::class,
                        'info' => [
                            'pre_request' => false,
                            'requests' => [],
                            'request_count' => 3,
                            'mock_response' => [
                                Helper::jsonEncode([MetasyntacticVars::FOO => 0]),
                                Helper::jsonEncode([MetasyntacticVars::FOO => 1]),
                                Helper::jsonEncode([MetasyntacticVars::FOO => 2]),
                            ],
                        ],
                        'sub_operator' => null,
                    ]
                ]
            ],
            Executor::restore($executor->explain())->explain()
        );

        $i = 0;
        foreach ($executor->execute() as $data) {
            $this->assertEquals(
                [MetasyntacticVars::FOO => $i],
                $data
            );
            ++$i;
        }
    }
}
