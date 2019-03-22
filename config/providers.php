<?php

return [
    //Common Providers
    //

    //App Providers
    \App\components\datetime\DatetimeProvider::class,
    \App\components\utils\swoole\counter\CounterProvider::class,
    \App\components\event\EventProvider::class,

    //Request Providers
    \App\components\log\LogProvider::class,
    \App\components\redis\RedisProvider::class,
    \App\components\ratelimit\RatelimitProvider::class,
    \App\components\cache\CacheProvider::class,
    \App\components\mysql\MysqlProvider::class,
    \App\components\es\EsProvider::class,
    \App\components\storage\StorageProvider::class,
    \App\components\amqp\AmqpProvider::class,
    \App\components\hbase\HbaseProvider::class,

    //Shutdown Providers
    \App\components\utils\swoole\coresource\CoroutineResProvider::class,
];
