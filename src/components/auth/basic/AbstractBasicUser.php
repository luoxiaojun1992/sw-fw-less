<?php

namespace SwFwLess\components\auth\basic;

use SwFwLess\models\AbstractPDOModel;

abstract class AbstractBasicUser extends AbstractPDOModel implements UserProviderContract
{
    abstract public function retrieveByToken($user, $pwd);
}
