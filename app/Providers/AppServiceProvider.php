<?php
namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;
use URL;

class AppServiceProvider extends ServiceProvider {


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // set http schema (force f.e. https)
        URL::forceScheme(config('app.force_scheme', 'http'));
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Blade::directive('duration', function ($expression) {
            return "<?php echo e(\\Jenssegers\\Date\\Date::createFromTimestamp(0)->diff(\\Jenssegers\\Date\\Date::createFromTimestamp({$expression}), true)->format('%H:%I:%S')); ?>";
        });
    }
}
