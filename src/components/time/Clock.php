<?php

namespace SwFwLess\components\time;

use SwFwLess\components\Helper;
use SwFwLess\components\http\Client;
use SwFwLess\components\utils\data\structure\Arr;

class Clock
{
    protected $nodeTimeApiUrl;

    protected $swfRequest;

    protected $nodeTimeApiTraceInfo;

    public static function create($nodeTimeApiUrl, $swfRequest = null)
    {
        return new static($nodeTimeApiUrl, $swfRequest);
    }

    public function __construct($nodeTimeApiUrl, $swfRequest = null)
    {
        $this->nodeTimeApiUrl = $nodeTimeApiUrl;
        $this->swfRequest = $swfRequest;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function measureNodesTimeOffset()
    {
        $nodeTime = $this->fetchNodeTime();
        $sendTime = $nodeTime['sendTime'];
        $receiveTime = $nodeTime['receiveTime'];
        $rtt = $nodeTime['rtt'];
        return \SwFwLess\components\utils\Datetime::nodesTimeOffset($sendTime, $receiveTime, $rtt);
    }

    public function setNodeTimeApiTraceFlag($withTrace = false)
    {
        $this->nodeTimeApiTraceInfo['with_trace'] = $withTrace;
        return $this;
    }

    public function setNodeTimeApiSpanName($spanName = null)
    {
        $this->nodeTimeApiTraceInfo['span_name'] = $spanName;
        return $this;
    }

    public function setNodeTimeApiInjectSpanCtx($injectSpanCtx = true)
    {
        $this->nodeTimeApiTraceInfo['inject_span_ctx'] = $injectSpanCtx;
        return $this;
    }

    public function setNodeTimeApiFlushingTrace($flushingTrace = false)
    {
        $this->nodeTimeApiTraceInfo['flushing_trace'] = $flushingTrace;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function fetchNodeTime()
    {
        $sendTime = microtime(true);

        $apiResponse = Client::get(
            $this->nodeTimeApiUrl, $this->swfRequest, [], null,
            Arr::arrGet($this->nodeTimeApiTraceInfo, 'with_trace', false),
            Arr::arrGet($this->nodeTimeApiTraceInfo, 'span_name', null),
            Arr::arrGet($this->nodeTimeApiTraceInfo, 'inject_span_ctx', true),
            Arr::arrGet($this->nodeTimeApiTraceInfo, 'flushing_trace', false)
        );

        if (!isset($apiResponse[0])) {
            throw new \Exception('Empty node time api response');
        }

        $apiResponseArr = Helper::jsonDecode($apiResponse[0]->getBody());
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                'Empty node time api response error, response: ' .
                ((string)($apiResponse[0]))
            );
        }

        if ($apiResponseArr['code'] !== 0) {
            throw new \Exception(
                'Empty node time api response error, response: ' .
                ($apiResponse[0])
            );
        }

        $receiveTime = $apiResponseArr['data']['timestamp'];

        $rtt = microtime(true) - $sendTime;

        return compact('sendTime', 'receiveTime', 'rtt');
    }
}
