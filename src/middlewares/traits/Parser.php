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
            $result = explode(':', $middlewareName);
        } else {
            $result = [$middlewareName, null];
        }

        $result[0] = config('middleware.aliases')[$result[0]] ?? $result[0];

        return $result;
    }
}
