<?php

$providers = [
    //Common Providers
    \App\components\swoole\counter\CounterProvider::class,

    //App Providers
    \App\components\datetime\DatetimeProvider::class,
    \App\components\chaos\ChaosProvider::class,

    //Request Providers
    \App\components\event\EventProvider::class,
    \App\components\log\LogProvider::class,
    \App\components\redis\RedisProvider::class,
    \App\components\ratelimit\RatelimitProvider::class,
    \App\components\cache\CacheProvider::class,
    \App\components\mysql\MysqlProvider::class,
    \App\components\es\EsProvider::class,
    \App\components\storage\StorageProvider::class,
    \App\components\amqp\AmqpProvider::class,
    \App\components\hbase\HbaseProvider::class,
    \App\components\di\ContainerProvider::class,

    //Shutdown Providers
    \App\components\swoole\coresource\CoroutineResProvider::class,
];

$composerInstalled = file_get_contents(__DIR__ . '/../vendor/composer/installed.json');
$packages = json_decode($composerInstalled, true);
foreach ($packages as $package) {
    if (isset($package['extra']['sw-fw-less']['provider'])) {
        array_push($providers, $package['extra']['sw-fw-less']['provider']);
    }
}

return $providers;
