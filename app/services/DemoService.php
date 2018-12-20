<?php

namespace App\services;

use App\components\Helper;
use App\components\Response;
use App\models\Member;
use App\models\Test;
use Cake\Validation\Validator;
use Phalcon\Validation;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\IO\SocketIO;
use Swlib\SaberGM;

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
                        return \App\components\Validator::string($data['key'], $data);
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
                    'rule' => [\App\components\Validator::class, 'string'],
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
            ->where('id in (111426517, 111426518)')
            ->limit(2)
            ->first();

        return Response::json($queryResult);
    }

    public function http()
    {
        $res = SaberGM::get('http://news.baidu.com/widget?ajax=json&id=ad');

        return Response::json($res->getBody());
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

    public function qiniu()
    {
        if (file_exists('qiniu://musics/test2.txt')) {
            unlink('qiniu://musics/test2.txt');
        }
        file_put_contents('qiniu://musics/test2.txt', 'test111111111111111111111111111');

        return Response::output(file_get_contents('qiniu://musics/test2.txt'));
    }

    public function rabbitmq()
    {
        //todo connection pool
        $connection = new AMQPSocketConnection('127.0.0.1', 32775, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('hello', false, false, false, false);
        $msg = new AMQPMessage('Hello World!');
        $channel->basic_publish($msg, '', 'hello');

        return Response::output("Sent 'Hello World!'");
    }
}
