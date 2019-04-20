<?php

namespace SwFwLess\components\zipkin;

use SwFwLess\components\http\Request;
use Zipkin\Propagation\Getter;
use Zipkin\Propagation\Setter;

final class SwfRequestHeaders implements Getter, Setter
{
    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     */
    public function get($carrier, $key)
    {
        $lKey = strtolower($key);
        return $carrier->hasHeader($lKey) ? $carrier->header($lKey) : null;
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function put(&$carrier, $key, $value)
    {
        $lKey = strtolower($key);
        if (!is_array($carrier->getSwRequest()->header)) {
            $carrier->getSwRequest()->header = [];
        }
        $carrier = $carrier->getSwRequest()->header[$lKey] = $value;
    }
}
