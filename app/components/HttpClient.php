<?php

namespace App\components;

use Swlib\SaberGM;
use Swoole\Coroutine\Channel;

class HttpClient
{
    public static function multiGet($urls)
    {
        if (extension_loaded('swoole')) {
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
        } else {
            //todo refactory with guzzlehttp

            $aggResult = [];
            foreach ($urls as $id => $url) {
                $aggResult[$id] = SaberGM::get($url);
            }

            return $aggResult;
        }
    }
}
