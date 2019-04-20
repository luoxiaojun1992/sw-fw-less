<?php

namespace SwFwLess\services;

use SwFwLess\components\http\Response;
use SwFwLess\facades\Jwt;

/**
 * Class DiningService
 *
 * {@inheritdoc}
 *
 * demo service for my demo app
 *
 * @package SwFwLess\services
 */
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
                $token = Jwt::issue(request(), ['id' => 1]);

                return Response::json(['data' => ['token' => (string)$token], 'code' => 0, 'msg' => 'ok']);
            }
        }

        return Response::json(['data' => [], 'code' => 1, 'msg' => 'fail']);
    }
}
