<?php

namespace App\models;

use App\components\auth\jwt\UserProviderContract;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class JwtUser extends AbstractMysqlModel implements UserProviderContract
{
    public function retrieveByToken($authToken, $signKey, $jid)
    {
        if (!$authToken) {
            return false;
        }

        $token = (new Parser())->parse((string) $authToken);
        if (!$token->verify(new Sha256(), $signKey)) {
            return false;
        }

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer('http://example.com');
        $data->setAudience('http://example.org');
        $data->setId($jid);

        if ($result = $token->validate($data)) {
            $this->id = $token->getClaim('uid');
        }

        return $result;
    }
}
