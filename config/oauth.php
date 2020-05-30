<?php
$fromServices = require(config_path('services.php'));
$oauth = [
    // generic config, gets inherited from others if not defined independently
    'client_id' => env('OAUTH_CLIENT_ID'),
    'client_secret' => docker_secret(env('OAUTH_CLIENT_SECRET')),
    'redirect' => env('OAUTH_REDIRECT'),
    'driver' => env('OAUTH_DRIVER', 'google'),
];

if (isset($fromServices['github'])) {
    $oauth['github'] = $fromServices['github'];
}

if (isset($fromServices['google'])) {
    $oauth['google'] = $fromServices['google'];
}

if (isset($fromServices['linkedin'])) {
    $oauth['linkedin'] = $fromServices['linkedin'];
}

return $oauth;
