<?php

namespace SwFwLess\components\auth\jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class Jwt
{
    private static $instance;

    private $config = [];

    /**
     * @param array $config
     * @return self
     */
    public static function create($config = [])
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self($config);
    }

    /**
     * Jwt constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param null $swfRequest
     * @param array $payload
     * @return \Lcobucci\JWT\Token
     */
    public function issue($swfRequest = null, $payload = [])
    {
        $signer = new Sha256();
        $time = time();

        $swfRequest = $swfRequest ?? request();
        $host = $swfRequest->convertToPsr7()->getUri()->getHost();

        $tokenBuilder = (new Builder())->setIssuer($host) // Configures the issuer (iss claim)
        ->setAudience($host) // Configures the audience (aud claim)
        ->setId($this->config['jid'], true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt($time) // Configures the time that the token was issue (iat claim)
        ->setNotBefore($time) // Configures the time that the token can be used (nbf claim)
        ->setExpiration($time + 86400); // Configures the expiration time of the token (exp claim)

        foreach ($payload as $key => $value) {
            $tokenBuilder->set($key, $value);
        }

        return $tokenBuilder->sign($signer, $this->config['sign_key'])->getToken();
    }

    /**
     * @param $tokenStr
     * @param null $swfRequest
     * @return \Lcobucci\JWT\Token|null
     */
    public function validate($tokenStr, $swfRequest = null)
    {
        if (!$tokenStr) {
            return null;
        }

        $swfRequest = $swfRequest ?? request();
        $host = $swfRequest->convertToPsr7()->getUri()->getHost();

        $token = (new Parser())->parse((string) $tokenStr);
        if (!$token->verify(new Sha256(), $this->config['sign_key'])) {
            return null;
        }

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($host);
        $data->setAudience($host);
        $data->setId($this->config['jid']);

        if ($result = $token->validate($data)) {
            return $token;
        }

        return null;
    }
}
