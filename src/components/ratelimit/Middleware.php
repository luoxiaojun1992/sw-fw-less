<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\http\Request;
use SwFwLess\components\http\Response;
use SwFwLess\middlewares\AbstractMiddleware;
use SwFwLess\middlewares\traits\Parser;

class Middleware extends AbstractMiddleware
{
    use Parser;
    
    private $config = [];

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function handle(Request $request)
    {
        $options = $this->parseOptions(['period', 'throttle']);
        $throttleConfig = config('throttle');
        if (isset($options['period']) && $options['period'] !== '') {
            $throttleConfig['period'] = intval($options['period']);
        }
        if (isset($options['throttle']) && $options['throttle'] !== '') {
            $throttleConfig['throttle'] = intval($options['throttle']);
        }
        $this->config = $throttleConfig;

        list($metric, $period, $throttle) = $this->parseConfig($request);

        if (!RateLimit::create()->pass($metric, $period, $throttle, $remaining)) {
            return Response::output('', 429)->header('X-RateLimit-Period', $period)
                ->header('X-RateLimit-Throttle', $throttle)
                ->header('X-RateLimit-Remaining', $remaining);
        }

        return $this->next()->header('X-RateLimit-Throttle', $throttle)->header('X-RateLimit-Remaining', $remaining);
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
}
