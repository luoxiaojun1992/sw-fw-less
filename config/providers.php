<?php

return [
    //Common Providers
    \App\components\swoole\counter\CounterProvider::class,

    //App Providers
    \App\components\datetime\DatetimeProvider::class,
    \App\components\chaos\ChaosProvider::class,
    \App\components\event\EventProvider::class,

    //Request Providers
    \App\components\log\LogProvider::class,
    \App\components\redis\RedisProvider::class,
    \App\components\ratelimit\RatelimitProvider::class,
    \App\components\cache\CacheProvider::class,
    \App\components\mysql\MysqlProvider::class,
    \App\components\amqp\AmqpProvider::class,
    \App\components\hbase\HbaseProvider::class,

    //Shutdown Providers
    \App\components\swoole\coresource\CoroutineResProvider::class,
];
