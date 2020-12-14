<?php

namespace SwFwLess\components\auth;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;
use SwFwLess\middlewares\traits\Parser;

class Middleware extends AbstractMiddleware
{
    use Parser;

    public function handle(Request $request)
    {
        $config = \SwFwLess\components\functions\config('auth');

        $options = $this->parseOptions(['guardName', 'userProvider', 'credentialKey']);
        $guardName = !empty($options['guardName']) ? $options['guardName'] : $config['guard'];

        if (!empty($options['userProvider'])) {
            $config['guards'][$guardName]['user_provider'] = $options['userProvider'];
        }
        if (!empty($options['credentialKey'])) {
            $config['guards'][$guardName]['credential_key'] = $options['credentialKey'];
        }

        if (!Auth::verify($request, $guardName, $config)) {
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
}
