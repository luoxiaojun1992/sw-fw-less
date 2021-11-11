<?php

namespace SwFwLess\services;

use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Message\AMQPMessage;
use SwFwLess\components\amqp\ConnectionWrapper;
use SwFwLess\components\Config;
use SwFwLess\components\Helper;
use SwFwLess\components\http\Client;
use SwFwLess\components\http\Response;
use SwFwLess\components\time\ntp\Time;
use SwFwLess\components\utils\data\structure\variable\MetasyntacticVars;
use SwFwLess\components\volcano\Executor;
use SwFwLess\components\volcano\http\extractor\ResponseExtractor;
use SwFwLess\components\volcano\http\HttpRequest;
use SwFwLess\components\volcano\serializer\json\Decoder;
use SwFwLess\facades\Alioss;
use SwFwLess\facades\AMQPConnectionPool;
use SwFwLess\facades\Cache;
use SwFwLess\facades\Container;
use SwFwLess\facades\File;
use SwFwLess\facades\HbasePool;
use SwFwLess\facades\Jwt;
use SwFwLess\facades\Log;
use SwFwLess\facades\Math;
use SwFwLess\facades\Qiniu;
use SwFwLess\facades\RedisPool;
use SwFwLess\facades\Translator;
use SwFwLess\models\TestPDO;
use SwFwLess\models\TestES;
use Cake\Validation\Validator;
use Hbase\HbaseClient;
use Phalcon\Validation;

class DemoService extends BaseService
{
    public function ping()
    {
        return 'pong';
    }

    public function zipkin()
    {
        return Container::callWithTrace([Response::class, 'output'], ['pong']);
    }

    public function redis()
    {
        $params = $this->getRequest()->all();

        //Param Validation
        if (extension_loaded('phalcon')) {
            $messages = (new Validation())->add('key', new Validation\Validator\PresenceOf(['message' => 'key is required']))
                ->add('key', new Validation\Validator\StringLength(['min' => 1, 'max' => 10, 'message' => 'key is not between 1 t0 10']))
                ->add('key', new Validation\Validator\Callback([
                    'callback' => function($data) {
                        return \SwFwLess\components\Validator::string($data['key'], $data);
                    },
                    'message' => 'key is not a string',
                ]))->validate($params);
            $errors = [];
            foreach ($messages as $message) {
                $errors[] = $message->getMessage();
            }
        } else {
            $errors = (new Validator())->requirePresence('key')
                ->lengthBetween('key', [1, 10])
                ->add('key', 'string', [
                    'rule' => [\SwFwLess\components\Validator::class, 'string'],
                    'message' => 'key is not a string'
                ])->errors($params);
        }
        if (count($errors) > 0) {
            return Response::json(['code' => 1, 'msg' => Helper::jsonEncode($errors), 'data' => []]);
        }

        $redis = RedisPool::pick();
        try {
            $result = $redis->get($params['key']);
            Log::info('test error');
            return Response::json(['code' => 0, 'msg' => 'ok', 'data' => $result]);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            RedisPool::release($redis);
        }
    }

    public function mysql()
    {
        $queryResult = TestPDO::select()
            ->cols(['*'])
            ->where('id in (1, 2)')
            ->limit(2)
            ->first();

        return Response::json($queryResult);
    }

    public function http()
    {
        $start = microtime(true);

        $urls = [
            'a' => 'http://127.0.0.1:9501/ping',
            'b' => 'http://127.0.0.1:9501/ping',
            'c' => 'http://127.0.0.1:9501/ping',
        ];

        $aggResult = Client::multiGet($urls);

        var_dump(microtime(true) - $start);

        $aggResult = array_map(function ($v) {
            return (string)$v->getBody();
        }, $aggResult);

        return Response::json($aggResult);
    }

    public function postJson()
    {
        return Response::json([MetasyntacticVars::FOO => $this->getRequest()->body()]);
    }

    public function volcano()
    {
        $executor = Executor::create()->setPlan(
            Decoder::create()->setNext(
                ResponseExtractor::create()->setNext(
                    HttpRequest::create()->setSwfRequest($this->getRequest())
                        ->addRequest(
                            'http://127.0.0.1:9501/postjson',
                            'POST',
                            [],
                            'bar',
                            Client::STRING_BODY
                        )
                        ->addRequest(
                            'http://127.0.0.1:9501/postjson',
                            'POST',
                            [],
                            'bar',
                            Client::STRING_BODY
                        )
                        ->addRequest(
                            'http://127.0.0.1:9501/postjson',
                            'POST',
                            [],
                            'bar',
                            Client::STRING_BODY
                        )
                )
            )
        );
        $plan = $executor->explain();
        $data = [];
        foreach ($executor->execute() as $subData) {
            $data[] = $subData;
        }

        return Response::json([
            'plan' => $plan,
            'data' => $data,
        ]);
    }

    public function es()
    {
        $models = TestES::query()->filterTerm('foo', 'bar')->search();

        return Response::json($models);
    }

    public function file()
    {
        $storage = File::prepare();
        $storage->put('test.txt', 'test1111111111111');
        return Response::output($storage->read('test.txt'));
    }

    public function qiniu()
    {
        $storage = Qiniu::prepare();
        $storage->put('musics/test2.txt', 'test111111111111111111111111111');
        return Response::output($storage->read('musics/test2.txt'));
    }

    public function rabbitmq()
    {
        $data = 'Hello World!';

        $queueName = AMQPConnectionPool::getQueue('hello');
        $do = function ($channel) use ($queueName, $data) {
            $channel->queue_declare(
                $queueName,
                false,
                true,
                false,
                false
            );
            $msg = new AMQPMessage($data);
            $channel->basic_publish($msg, '', $queueName);
        };

        $channel = null;
        $channel_id = Config::get('amqp.channel_id');
        /** @var AMQPSocketConnection|ConnectionWrapper $connection */
        $connection = AMQPConnectionPool::pick();
        try {
            $channel = $connection->channel($channel_id);
            $do($channel);
        } catch (\Throwable $e) {
            if ($connection->causedByLostConnection($e)) {
                $realConnection = $connection->getConnection();
                $realConnection->reconnect();
                $channel = $realConnection->channel($channel_id);
                $do($channel);
            }
            throw $e;
        } finally {
            AMQPConnectionPool::release($connection);
        }

        return Response::output("Sent '" . $data . "'");
    }

    public function alioss()
    {
        $storage = Alioss::prepare();
        $storage->write('sw-fw-less/test2.txt', 'test111111111111111111111111111');
        return Response::output($storage->read('sw-fw-less/test2.txt'));
    }

    /**
     * @return Response
     * @throws \Throwable
     */
    public function hbase()
    {
        $tables = [];

        /** @var HbaseClient $client */
        $client = HbasePool::pick();

        try {
            $tables = $client->getTableNames();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            HbasePool::release($client);
        }

        return Response::json(['tables' => $tables]);
    }

    public function cache()
    {
        Cache::set('foo', 'bar', 10);
        return Response::output(Cache::get('foo'));
    }

    public function jwt()
    {
        $token = Jwt::issue(\SwFwLess\components\functions\request(), ['id' => 1]);

        return Response::json(['data' => ['token' => (string)$token], 'code' => 0, 'msg' => 'ok']);
    }

    public function log()
    {
        for ($i = 0; $i < 10; ++$i) {
            Log::info('test log');
        }

        return Response::json(['code' => 0]);
    }

    public function math()
    {
        $cNumbers = Math::createCNumbers(100000);
        for ($i = 1; $i <= 100000; ++$i) {
            $cNumbers[$i - 1] = $i;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
            $vector2[$i] = $i + 1;
        }
        $cVectorSum = Math::vectorAdd($vector1, $vector2, $numbersCount);
        $vectorSum = [];
        foreach ($cVectorSum as $elementSum) {
            $vectorSum[] = $elementSum;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
            $vector2[$i] = $i + 1;
        }
        $cVectorDiff = Math::vectorSub($vector1, $vector2, $numbersCount);
        $vectorDiff = [];
        foreach ($cVectorDiff as $elementDiff) {
            $vectorDiff[] = $elementDiff;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
            $vector2[$i] = $i + 1;
        }
        $cVectorProduct = Math::vectorMul($vector1, $vector2, $numbersCount);
        $vectorProduct = [];
        foreach ($cVectorProduct as $elementProduct) {
            $vectorProduct[] = $elementProduct;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
            $vector2[$i] = $i + 1;
        }
        $cVectorDiv = Math::vectorDiv($vector1, $vector2, $numbersCount);
        $vectorDiv = [];
        foreach ($cVectorDiv as $elementDiv) {
            $vectorDiv[] = $elementDiv;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = pow($i + 1, 2);
        }
        $cVectorRoot = Math::vectorSqrt($vector1, $numbersCount);
        $vectorRoot = [];
        foreach ($cVectorRoot as $elementRoot) {
            $vectorRoot[] = $elementRoot;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
            $vector2[$i] = $i + 1;
        }
        $cVectorCmp = Math::vectorCmp($vector1, $vector2, $numbersCount);
        $vectorCmp = [];
        foreach ($cVectorCmp as $elementCmp) {
            if (is_nan($elementCmp)) {
                $vectorCmp[] = 1;
            } else {
                $vectorCmp[] = $elementCmp;
            }
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
            $vector2[$i] = $i + 2;
        }
        $cVectorCmp = Math::vectorCmp($vector1, $vector2, $numbersCount);
        $vectorCmp2 = [];
        foreach ($cVectorCmp as $elementCmp) {
            if (is_nan($elementCmp)) {
                $vectorCmp2[] = 1;
            } else {
                $vectorCmp2[] = $elementCmp;
            }
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        $vector2 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 2;
            $vector2[$i] = $i + 1;
        }
        $cVectorCmp = Math::vectorCmp($vector1, $vector2, $numbersCount);
        $vectorCmp3 = [];
        foreach ($cVectorCmp as $elementCmp) {
            if (is_nan($elementCmp)) {
                $vectorCmp3[] = 1;
            } else {
                $vectorCmp3[] = $elementCmp;
            }
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = $i + 1;
        }
        $cVectorRcp = Math::vectorRcp($vector1, $numbersCount);
        $vectorRcp = [];
        foreach ($cVectorRcp as $elementRcp) {
            $vectorRcp[] = $elementRcp;
        }

        $numbersCount = 4;
        $vector1 = Math::createCIntNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = -1 * ($i + 1);
        }
        $cVectorAbs = Math::vectorAbs($vector1, $numbersCount);
        $vectorAbs = [];
        foreach ($cVectorAbs as $elementAbs) {
            $vectorAbs[] = $elementAbs;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = ($i + 1 + 0.3);
        }
        $cVectorCeil = Math::vectorCeil($vector1, $numbersCount);
        $vectorCeil = [];
        foreach ($cVectorCeil as $elementCeil) {
            $vectorCeil[] = $elementCeil;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = ($i + 1 + 0.3);
        }
        $cVectorFloor = Math::vectorFloor($vector1, $numbersCount);
        $vectorFloor = [];
        foreach ($cVectorFloor as $elementFloor) {
            $vectorFloor[] = $elementFloor;
        }

        $numbersCount = 4;
        $vector1 = Math::createCFloatNumbers($numbersCount);
        for ($i = 0; $i < $numbersCount; ++$i) {
            $vector1[$i] = ($i + 1 + 0.3);
        }
        $cVectorRound = Math::vectorRound($vector1, $numbersCount);
        $vectorRound = [];
        foreach ($cVectorRound as $elementRound) {
            $vectorRound[] = $elementRound;
        }

        return Response::json(
            [
                'sum' => Math::sum(null, 100000, $cNumbers),
                'vector_sum' => $vectorSum,
                'vector_diff' => $vectorDiff,
                'vector_product' => $vectorProduct,
                'vector_div' => $vectorDiv,
                'vector_root' => $vectorRoot,
                'vector_cmp' => $vectorCmp,
                'vector_cmp2' => $vectorCmp2,
                'vector_cmp3' => $vectorCmp3,
                'vector_rcp' => $vectorRcp,
                'vector_abs' => $vectorAbs,
                'vector_ceil' => $vectorCeil,
                'vector_floor' => $vectorFloor,
                'vector_round' => $vectorRound,
            ]
        );
    }

    /**
     * @return Response
     * @throws \Throwable
     */
    public function ntp()
    {
        return Response::json(['timestamp' => Time::create()->getTimestamp()]);
    }

    /**
     * @return Response
     */
    public function translate()
    {
        return Response::json(
            [
                'Hello world' => Translator::trans('Hello world', [], 'app', 'zh_CN')
            ]
        );
    }
}
