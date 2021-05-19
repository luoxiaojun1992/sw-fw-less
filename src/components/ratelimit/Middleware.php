<?php

namespace SwFwLess\components\ratelimit;

use SwFwLess\components\http\Code;
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
        $throttleConfig = \SwFwLess\components\functions\config('throttle');
        $options = $this->parseOptions(['period', 'throttle', 'back', 'driver']);
        if (isset($options['period']) && $options['period'] !== '') {
            $throttleConfig['period'] = intval($options['period']);
        }
        if (isset($options['throttle']) && $options['throttle'] !== '') {
            $throttleConfig['throttle'] = intval($options['throttle']);
        }
        if (isset($options['back']) && $options['back'] !== '') {
            $throttleConfig['back'] = boolval(intval($options['back']));
        }
        if (isset($options['driver']) && $options['driver'] !== '') {
            $throttleConfig['driver'] = $options['driver'];
        }
        $this->config = $throttleConfig;

        list($metric, $period, $throttle, $giveBack, $driver) = $this->parseConfig($request);

        $rateLimit = $driver ? RateLimitFactory::resolve($driver) : RateLimitFactory::resolve();

        if (!$rateLimit->pass($metric, $period, $throttle, $remaining)) {
            if ($giveBack) {
                if ($rateLimit->supportGivingBack()) {
                    $remaining = $rateLimit->giveBack($metric, $period, $throttle);
                }
            }

            return Response::output('', Code::STATUS_TOO_MANY_REQUESTS)
                ->header('X-RateLimit-Period', $period)
                ->header('X-RateLimit-Throttle', $throttle)
                ->header('X-RateLimit-Remaining', $remaining);
        }

        if ($giveBack) {
            if ($rateLimit->supportGivingBack()) {
                $remaining = $rateLimit->giveBack($metric, $period, $throttle);
            }
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

        return [
            $metric ?: $request->uri(),
            $this->config['period'],
            $this->config['throttle'],
            ($this->config['back']) ?? false,
            ($this->config['driver']) ?? null
        ];
    }
}
