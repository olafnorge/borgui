<?php
namespace App\Providers;

use Rollbar\Laravel\RollbarServiceProvider as BaseRollbarServiceProvider;

class RollbarServiceProvider extends BaseRollbarServiceProvider {


    /**
     * @param string $key
     * @param null $default
     * @return mixed|string
     */
    public static function config($key = '', $default = null) {
        $parent = parent::config($key, $default);

        return is_string($parent) ? docker_secret($parent) : $parent;
    }
}
