<?php

namespace App\components\http;

use Swlib\SaberGM;
use Swoole\Coroutine\Channel;

class Client
{
    public static function multiGet($urls)
    {
        $requestCount = count($urls);

        $chan = new Channel($requestCount);

        $aggResult = [];
        foreach ($urls as $id => $url) {
            go(
                function () use (&$aggResult, $id, $url, $chan) {
                    $aggResult[$id] = SaberGM::get($url);
                    $chan->push(1);
                }
            );
        }

        for ($i = 0; $i < $requestCount; ++$i) {
            $chan->pop();
        }
        $chan->close();

        return $aggResult;
    }
}
