<?php

namespace SwFwLess\middlewares\traits;

trait Parser
{
    /**
     * @param $middlewareName
     * @return array
     */
    private function parseMiddlewareName($middlewareName)
    {
        if (strpos($middlewareName, ':') > 0) {
            return explode(':', $middlewareName);
        }

        return [$middlewareName, null];
    }
}
