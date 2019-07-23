<?php

namespace SwFwLess\services;

use SwFwLess\components\Helper;
use SwFwLess\components\http\Client;
use SwFwLess\components\http\Response;
use SwFwLess\facades\Cache;
use SwFwLess\facades\HbasePool;
use SwFwLess\facades\Jwt;
use SwFwLess\facades\Log;
use SwFwLess\models\Member;
use SwFwLess\models\Test;
use Cake\Validation\Validator;
use Hbase\HbaseClient;
use Phalcon\Validation;

class DemoService extends BaseService
{
    public function ping()
    {
        return Response::output('pong');
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

//        unlink('redis://' . $params['key']);
//        file_put_contents('redis://' . $params['key'], 'value2');

        $result = file_get_contents('redis://' . $params['key']);

        file_put_contents('log://info', 'test error');

        return Response::json(['code' => 0, 'msg' => 'ok', 'data' => $result]);
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
        if (file_exists('storage://test.txt')) {
            unlink('storage://test.txt');
        }
        file_put_contents('storage://test.txt', 'test1111111111111');

        return Response::output(file_get_contents('storage://test.txt'));
    }

//    public function qiniu()
//    {
//        if (file_exists('qiniu://musics/test2.txt')) {
//            unlink('qiniu://musics/test2.txt');
//        }
//        file_put_contents('qiniu://musics/test2.txt', 'test111111111111111111111111111');
//
//        return Response::output(file_get_contents('qiniu://musics/test2.txt'));
//    }

    public function rabbitmq()
    {
        file_put_contents('amqp://hello', 'Hello World!');

        return Response::output("Sent 'Hello World!'");
    }

    public function alioss()
    {
        if (file_exists('alioss://sw-fw-less/test2.txt')) {
            unlink('alioss://sw-fw-less/test2.txt');
        }
        file_put_contents('alioss://sw-fw-less/test2.txt', 'test111111111111111111111111111');

        return Response::output(file_get_contents('alioss://sw-fw-less/test2.txt'));
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
        $token = Jwt::issue(request(), ['id' => 1]);

        return Response::json(['data' => ['token' => (string)$token], 'code' => 0, 'msg' => 'ok']);
    }

    public function log()
    {
        for ($i = 0; $i < 10; ++$i) {
            Log::info('test log');
        }

        return Response::json(['code' => 0]);
    }
}
