<?php

namespace App\middlewares;

use App\components\Request;

class RedLock extends AbstractMiddleware
{
    /**
     * @param Request $request
     * @return \App\components\Response|mixed
     */
    public function handle(Request $request)
    {
        \App\facades\RedLock::flushAll();

        return $this->next();
    }
}
