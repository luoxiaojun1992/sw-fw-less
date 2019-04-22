<?php

namespace SwFwLess\components\auth;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $config = config('auth');

        $options = $this->parseOptions();
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

    /**
     * @return array
     */
    protected function parseOptions()
    {
        $parsedOptions = [];
        if ($this->getOptions()) {
            $options = explode(',' , $this->getOptions());

            if (isset($options[0])) {
                $parsedOptions['guardName'] = $options[0];
            }
            if (isset($options[1])) {
                $parsedOptions['userProvider'] = $options[1];
            }
            if (isset($options[2])) {
                $parsedOptions['credentialKey'] = $options[2];
            }
        }

        return $parsedOptions;
    }
}
