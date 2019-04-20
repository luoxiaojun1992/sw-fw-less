<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\Config;
use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;

class Middleware extends AbstractMiddleware
{
    private $config = [];

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request)
    {
        $this->config = array_merge(Config::get('throttle'), $this->parseOptions());

        list($metric, $period, $throttle) = $this->parseConfig($request);

        if (!RateLimit::create()->pass($metric, $period, $throttle, $remaining)) {
            return Response::output('', 429)->header('X-RateLimit-Period', $period)
                ->header('X-RateLimit-Throttle', $throttle)
                ->header('X-RateLimit-Remaining', $remaining);
        }

        return $this->next();
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function parseConfig(Request $request)
    {
        if (is_callable($this->config['metric'])) {
            $metric = call_user_func_array($this->config['metric'], compact('request'));
        } else {
            $metric = $this->config['metric'];
        }

        return [$metric ?: $request->uri(), $this->config['period'], $this->config['throttle']];
    }

    /**
     * @return array
     */
    protected function parseOptions()
    {
        if ($this->getOptions()) {
            list($period, $throttle) = explode(',' , $this->getOptions());
            return compact('period', 'throttle');
        }

        return [];
    }
}
