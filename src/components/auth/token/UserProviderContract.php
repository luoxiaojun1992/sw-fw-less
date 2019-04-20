<?php

namespace SwFwLess\components\auth\token;

interface UserProviderContract
{
    public function retrieveByToken($authToken);
}
