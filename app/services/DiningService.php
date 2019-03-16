<?php

namespace App\services;

use App\components\http\Response;
use App\components\RedisWrapper;
use App\facades\RedisPool;

class DiningService extends BaseService
{
    public function menu()
    {
        if (request()->get('page') < 6) {
            if (request()->get('keyword')) {
                return Response::json(
                    [
                        'data' => [
                            [
                                'name' => '毛血旺',
                                'id' => 1,
                            ],
                            [
                                'name' => '酸菜鱼片',
                                'id' => 2,
                            ],
                            [
                                'name' => '干锅童子鸡',
                                'id' => 3,
                            ]
                        ],
                        'code' => 0,
                        'msg' => 'ok',
                    ]
                );
            }
            return Response::json(
                [
                    'data' => [
                        [
                            'name' => '毛血旺',
                            'id' => 1,
                        ],
                        [
                            'name' => '酸菜鱼片',
                            'id' => 2,
                        ],
                        [
                            'name' => '干锅童子鸡',
                            'id' => 3,
                        ],
                        [
                            'name' => '熏鱼',
                            'id' => 4,
                        ],
                        [
                            'name' => '清炒菠菜',
                            'id' => 5,
                        ],
                        [
                            'name' => '香椿炒鹅蛋',
                            'id' => 6,
                        ],
                        [
                            'name' => '凉拌海蜇皮',
                            'id' => 7,
                        ],
                    ],
                    'code' => 0,
                    'msg' => 'ok',
                ]
            );
        }

        return Response::json(['data' => [], 'code' => 0, 'msg' => 'ok']);
    }

    public function ordered()
    {
        if (request()->get('page') < 6) {
            if (request()->get('keyword')) {
                return Response::json(
                    [
                        'data' => [
                            [
                                'name' => '毛血旺()',
                            ],
                            [
                                'name' => '酸菜鱼片',
                            ],
                            [
                                'name' => '干锅童子鸡',
                            ]
                        ],
                        'code' => 0,
                        'msg' => 'ok',
                    ]
                );
            }
            return Response::json(
                [
                    'data' => [
                        [
                            'name' => '毛血旺',
                        ],
                        [
                            'name' => '酸菜鱼片',
                        ],
                        [
                            'name' => '干锅童子鸡',
                        ],
                        [
                            'name' => '熏鱼',
                        ],
                        [
                            'name' => '清炒菠菜',
                        ],
                        [
                            'name' => '香椿炒鹅蛋',
                        ],
                        [
                            'name' => '凉拌海蜇皮',
                        ],
                    ],
                    'code' => 0,
                    'msg' => 'ok',
                ]
            );
        }

        return Response::json(['data' => [], 'code' => 0, 'msg' => 'ok']);
    }

    public function order()
    {
        return Response::json(['data' => [], 'code' => 0, 'msg' => 'ok']);
    }

    public function login()
    {
        $invitationCodes = [
            'hello',
            'world',
        ];

        $postData = \json_decode((string)request()->body(), true);
        if (!json_last_error()) {
            if (in_array($postData['code'], $invitationCodes)) {
                /** @var \Redis|RedisWrapper $redis */
                $redis = RedisPool::pick();
                try {
                    $lua = <<<EOF
local new_value=redis.call('setnx', KEYS[1], ARGV[1]);
if(new_value > 0) then 
redis.call('expire', KEYS[1], ARGV[2]) 
end
return new_value
EOF;
                    $redis->eval($lua, ['auth:token:xxxxxx', 1, 86400], 1);
                    if ($redis->get('auth:token:xxxxxx')) {
                        return Response::json(['data' => ['token' => 'xxxxxx'], 'code' => 0, 'msg' => 'ok']);
                    }
                } catch (\Exception $e) {
                    throw $e;
                } finally {
                    RedisPool::release($redis);
                }
            }
        }

        return Response::json(['data' => [], 'code' => 1, 'msg' => 'fail']);
    }
}
