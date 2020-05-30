<?php

use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\SemaphoreStore;

return [
    'default' => env('LOCK_DRIVER', 'semaphore'),
    'stores' => [
        'flock' => [
            'driver' => FlockStore::class,
            'lock_path' => storage_path('framework/cache'),
        ],
        'redis' => [
            'driver' => RedisStore::class,
            'connection' => env('LOCK_REDIS_CONNECTION', 'default'),
        ],
        'semaphore' => [
            'driver' => SemaphoreStore::class,
        ],
    ],
];
