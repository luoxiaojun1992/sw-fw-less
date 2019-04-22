<?php

namespace SwFwLess\components\ratelimit;

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
        $options = $this->parseOptions();
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
        $parsedOptions = [];
        if ($this->getOptions()) {
            $options = explode(',' , $this->getOptions());

            if (isset($options[0])) {
                $parsedOptions['period'] = $options[0];
            }
            if (isset($options[1])) {
                $parsedOptions['throttle'] = $options[1];
            }
        }

        return $parsedOptions;
    }
}
