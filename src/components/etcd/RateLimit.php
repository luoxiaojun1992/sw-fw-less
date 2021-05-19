<?php

namespace SwFwLess\components\etcd;

/**
 * Class RateLimit
 *
 * {@inheritdoc}
 *
 * @package SwFwLess\components\etcd
 */
class RateLimit
{
    private static $instance;

    /**
     * @var Client
     */
    private $etcd;

    private $config = [
        'metric_prefix' => 'rate_limit:',
    ];

    /**
     * @param Client $etcd
     * @param array $config
     * @return static
     */
    public static function create(Client $etcd = null, $config = [])
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        if (!is_null($etcd)) {
            return self::$instance = new self($etcd, $config);
        }

        return null;
    }

    /**
     * RateLimit constructor.
     * @param Client $etcd
     * @param array $config
     */
    public function __construct(Client $etcd, $config = [])
    {
        $this->etcd = $etcd;
        $this->config = array_merge($this->config, $config);
    }

    protected function metricPrefix()
    {
        return $this->config['metric_prefix'];
    }

    protected function metricWithPrefix($metric)
    {
        return $this->metricPrefix() . $metric;
    }

    /**
     * @param $metric
     * @param $period
     * @param $throttle
     * @param $remaining
     * @return bool
     * @throws \Throwable
     */
    public function pass($metric, $period, $throttle, &$remaining = null)
    {
        $metricWithPrefix = $this->metricWithPrefix($metric);

        if (intval($this->etcd->get($metricWithPrefix)) >= $throttle) {
            return false;
        }

        $passed = $this->etcd->incr($metricWithPrefix, $period);
        if ($passed === false) {
            return false;
        }

        $remaining = $throttle - $passed;
        return ($passed > 0) && ($passed <= $throttle);
    }

    public function giveBack($metric, $period, $throttle)
    {
        //todo
        return 0;
    }

    /**
     * @param $metric
     * @return bool
     */
    public function clear($metric)
    {
        $metricWithPrefix = $this->metricWithPrefix($metric);

        return $this->etcd->del($metricWithPrefix);
    }
}
