<?php

namespace SwFwLessTest\components\auth\basic;

use PHPUnit\Framework\TestCase;
use SwFwLess\components\auth\basic\Guard;
use SwFwLessTest\stubs\components\auth\basic\UserProvider;

class GuardTest extends TestCase
{
    /**
     * @return SwRequest
     */
    private function createSwRequest()
    {
        require_once __DIR__ . '/../../../stubs/runtime/swoole/http/SwRequest.php';
        return new \SwRequest();
    }

    /**
     * @param null $swRequest
     * @return \SwFwLess\components\http\Request
     */
    private function createSwfRequest($swRequest = null)
    {
        require_once __DIR__ . '/../../../stubs/components/http/Request.php';
        return \Request::fromSwRequest($swRequest ?? $this->createSwRequest());
    }

    private function createUserProvider()
    {
        require_once __DIR__ . '/../../../stubs/components/auth/basic/UserProvider.php';
        return new UserProvider();
    }

    public function testValidate()
    {
        $swRequest = $this->createSwRequest();
        $swRequest->header = [
            'authorization' => 'Bearer ' . base64_encode('username' . ':' . 'password'),
        ];

        $swfRequest = $this->createSwfRequest($swRequest);

        $userProvider = $this->createUserProvider();

        $this->assertTrue((new Guard())->validate(
            $swfRequest,
            'authorization',
            $userProvider,
            []
        ));

        $this->assertEquals('username', $userProvider->getUser()->username());
    }
}
