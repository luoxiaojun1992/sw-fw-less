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
        return new HttpRequest();
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

        $executor = (new Executor())->setPlan(
            (new Decoder())->setNext(
                (new ResponseExtractor())->setNext($httpRequest)
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
                            'request_count' => 3,
                        ],
                        'sub_operator' => null,
                    ]
                ]
            ],
            $executor->explain()
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
