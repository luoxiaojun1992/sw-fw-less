<?php

return [
    \App\components\RedisPool::EVENT_REDIS_POOL_CHANGE => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('redis.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:redis', $count);
            }
        },
    ],
    \App\components\MysqlPool::EVENT_MYSQL_POOL_CHANGE => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('mysql.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:mysql', $count);
            }
        },
    ],
    \App\components\amqp\ConnectionPool::EVENT_AMQP_POOL_CHANGE => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('amqp.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:amqp', $count);
            }
        },
    ],
    \App\components\hbase\HbasePool::EVENT_HBASE_POOL_CHANGE => [
        function (\Cake\Event\Event $event) {
            $count = $event->getData('count');

            if (\App\components\Config::get('hbase.report_pool_change')) {
                \App\components\utils\swoole\counter\Counter::incr('monitor:pool:hbase', $count);
            }
        },
    ],
];
