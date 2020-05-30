<?php
namespace App\Providers;

use App\Backup;
use App\Policies\BackupPolicy;
use App\Policies\RepositoryPolicy;
use App\Repository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider {

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Backup::class => BackupPolicy::class,
        Repository::class => RepositoryPolicy::class,
    ];


    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();
    }
}
