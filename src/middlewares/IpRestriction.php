<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\components\utils\Ip;

class IpRestriction extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $ipRestrictionConfig = config('ip_restriction');

        if (empty($ipRestrictionConfig['ips'])) {
            return $this->next();
        }

        if (empty($ipRestrictionConfig['api_prefix'])) {
            return $this->next();
        }

        $ips = explode(',', $ipRestrictionConfig['ips']);

        if ($options = $this->getOptions()) {
            $ips =  array_merge($ips, explode(',', $options));
        }

        $requestIp = (string)$this->requestIp($request);
        if (!Ip::checkIp($requestIp, $ips)) {
            return Response::output('', 403)->header('X-Request-Ip', $requestIp);
        }

        return $this->next();
    }

    protected function requestIp(Request $request)
    {
        return $request->realIp();
    }
}
