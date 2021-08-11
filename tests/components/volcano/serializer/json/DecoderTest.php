<?php

namespace SwFwLessTests\components\volcanoo\serializer\json;

use SwFwLess\components\Helper;
use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;
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

        $decoder = new Decoder();
        $decoder->setNext(
            (new ResponseExtractor())->setNext($httpRequest)
        );

        $i = 0;
        foreach ($decoder->next() as $data) {
            $this->assertEquals(
                [MetasyntacticVars::FOO => $i],
                $data
            );
            ++$i;
        }
    }
}
