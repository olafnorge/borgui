<?php

namespace App\Console;

use App\Jobs\ProcessRepositorySync;
use App\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];


    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // feed horizon with metrics
        $schedule
            ->command('horizon:snapshot')
            ->withoutOverlapping()
            ->onOneServer()
            ->everyFiveMinutes();

        // run the sync of the repos and backup archives
        if (config('borg.scheduler_enabled')) {
            $schedule
                ->call(function () {
                    Repository::each(function (Repository $repository) {
                        ProcessRepositorySync::dispatch($repository);
                    });
                })
                ->timezone('Europe/Berlin')
                ->name('sync-repo-and-backups')
                ->withoutOverlapping()
                ->onOneServer()
                ->hourlyAt(21)
                ->unlessBetween('3:30', '5:30');
        }
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
