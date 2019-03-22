<?php

namespace App\components\auth;

use App\components\http\Request;
use App\components\http\Response;
use App\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $config = config('auth');

        $guardName = $config['guard'];
        $config['guards'][$guardName] = array_merge($config['guards'][$guardName], $this->parseOptions());

        if (!Auth::verify($request, null, $config)) {
            $response = Response::output('', 401)
                ->header('X-Auth-Guard', $guardName)
                ->header('X-Auth-Key', $config['guards'][$guardName]['credential_key']);

            if ($guardName === 'basic') {
                $response->header('WWW-Authenticate', 'Basic realm="Basic authentication failed."');
            }

            return $response;
        }

        return $this->next();
    }

    /**
     * @return array
     */
    protected function parseOptions()
    {
        if ($this->getOptions()) {
            list($user_provider, $credential_key) = explode(',' , $this->getOptions());
            return compact('user_provider', 'credential_key');
        }

        return [];
    }
}
