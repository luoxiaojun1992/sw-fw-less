<?php

return [
    'middleware' => [
        \App\components\zipkin\Middleware::class,
        \App\components\chaos\Middleware::class,
        \App\middlewares\Cors::class,
//        \App\components\auth\Middleware::class,
    ],
    'routeMiddleware' => [
        \App\components\ratelimit\Middleware::class,
    ],
];
