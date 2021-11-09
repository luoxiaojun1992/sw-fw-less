<?php

namespace SwFwLessTest\stubs\components\auth\basic;

use SwFwLess\components\auth\basic\UserProviderContract;

class UserProvider implements UserProviderContract
{
    protected $user;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    public function retrieveByToken($user, $pwd)
    {
        return ($this->user = (new class($user){
            protected $username;

            public function username()
            {
                return $this->username;
            }

            /**
             * @param mixed $username
             */
            public function setUsername($username)
            {
                $this->username = $username;
                return $this;
            }

            public function __construct($username)
            {
                $this->setUsername($username);
            }
        }));
    }
}
