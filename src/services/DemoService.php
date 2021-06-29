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
use SwFwLess\models\Member;
use SwFwLess\models\Test;
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
        $queryResult = Member::select()
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

    public function es()
    {
        $models = Test::query()->filterTerm('foo', 'bar')->search();

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
        $cVectorProduct = Math::vectorMul($vector1, $vector2, $numbersCount);
        $vectorProduct = [];
        foreach ($cVectorProduct as $elementProduct) {
            $vectorProduct[] = $elementProduct;
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

        return Response::json(
            [
                'sum' => Math::sum(null, 100000, $cNumbers),
                'vector_sum' => $vectorSum,
                'vector_product' => $vectorProduct,
                'vector_root' => $vectorRoot,
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
