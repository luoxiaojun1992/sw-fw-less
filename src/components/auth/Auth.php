<?php

namespace SwFwLess\components\auth;

use SwFwLess\components\swoole\coresource\traits\CoroutineRes;

class Auth
{
    use CoroutineRes;

    public $userProvider;

    public $guard;

    public function __construct()
    {
        static::register($this);
    }

    /**
     * @param $userProvider
     * @return $this
     */
    public function setUserProvider($userProvider)
    {
        $this->userProvider = $userProvider;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserProvider()
    {
        return $this->userProvider;
    }

    /**
     * @param $guard
     * @return $this
     */
    public function setGuard($guard)
    {
        $this->guard = $guard;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGuard()
    {
        return $this->guard;
    }

    /**
     * @param $credentialCarrier
     * @param null $guardName
     * @param null $config
     * @return bool
     */
    public static function verify($credentialCarrier, $guardName = null, $config = null)
    {
        $config = $config ?? \SwFwLess\components\functions\config('auth');
        $guardName = $guardName ?? $config['guard'];
        $guardConfig = $config['guards'][$guardName];
        /** @var GuardContract $guard */
        $guard = new $guardConfig['guard'];

        $userProvider = new $guardConfig['user_provider'];

        $result = $guard->validate($credentialCarrier, $guardConfig['credential_key'], $userProvider, $guardConfig);
        if ($result) {
            (new static())->setGuard($guard)->setUserProvider($userProvider);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public static function user()
    {
        /** @var static $auth */
        $auth = static::fetch();

        return $auth ? $auth->getUserProvider() : null;
    }

    /**
     * @return mixed
     */
    public static function guard()
    {
        /** @var static $auth */
        $auth = static::fetch();

        return $auth ? $auth->getGuard() : null;
    }
}
