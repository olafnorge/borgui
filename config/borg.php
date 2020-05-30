<?php
$config = [];

// in order to use this config in CI "as is" we need to mimicry some stuff
if (function_exists('env')) {
    $config = [
        'storage_path' => env('BORG_STORAGE_PATH', storage_path('tmp')) ?: storage_path('tmp'),
        'scheduler_enabled' => env('BORG_SCHEDULER_ENABLED', false),
        'cache_store' => env('BORG_CACHE_STORE', 'database'),
        'mount_enabled' => env('BORG_MOUNT_ENABLED', false),
        'log_level' => sprintf('--%s', strtolower(env('BORG_LOG_LEVEL', env('LOG_LEVEL', 'error')))),
        'wait_for_lock' => env('BORG_WAIT_FOR_LOCK', 60),
        'lock_ttl' => env('BORG_LOCK_TTL', 14400), // 4 hours in seconds
    ];
}

return array_merge(
    [
        'version' => '1.1.10',
    ],
    $config
);
