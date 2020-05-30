<?php

namespace App\Providers;

use Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider {


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        if (config('horizon.notification_email')) {
            Horizon::routeMailNotificationsTo(config('horizon.notification_email'));
        }

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');

//         Horizon::night();
    }


    /**
     * Configure the Horizon authorization services.
     *
     * @return void
     */
    public function authorization() {
        parent::authorization();

        Horizon::auth(function ($request) {
            return config('horizon.dashboard_enabled', false) && Gate::check('viewHorizon', [$request->user()]);
        });
    }


    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate() {
        Gate::define('viewHorizon', function ($user) {
            return $user->horizon_allowed;
        });
    }
}
