<?php
namespace App\Jobs;

use App\Cache\LockableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ProcessSync implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        LockableTrait,
        Queueable,
        SerializesModels;

    /**
     * @return int
     */
    protected function getWaitForBorgLock(): int {
        return config('borg.wait_for_lock');
    }
}
