<?php

namespace SwFwLess\components\zipkin;

use SwFwLess\components\http\Request;
use SwFwLess\components\utils\data\structure\Arr;
use Zipkin\Propagation\Getter;
use Zipkin\Propagation\Setter;

final class GrpcRequestHeaders implements Getter, Setter
{
    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     */
    public function get($carrier, $key)
    {
        $lKey = strtolower($key);

        return (Arr::isAssoc($carrier)) ? ($carrier['options']['headers'][$lKey] ?? null) :
            ($carrier[2]['headers'][$lKey] ?? null);
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

        if (Arr::isAssoc($carrier)) {
            $carrier['options']['headers'][$lKey] = $value;
        } else {
            if (!isset($carrier[1])) {
                $carrier[1] = [];
            }
            $carrier[2]['headers'][$lKey] = $value;
        }
    }
}
