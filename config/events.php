<?php

return [
    'redis:pool:change' => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('redis.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:redis', $count);
            }
        },
    ],
    'mysql:pool:change' => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('mysql.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:mysql', $count);
            }
        },
    ],
    'amqp:pool:change' => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('amqp.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:amqp', $count);
            }
        },
    ],
    'hbase:pool:change' => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('hbase.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:hbase', $count);
            }
        },
    ],
];
