<?php

namespace App\Console\Commands;

use App\Console\ExitableTrait;
use App\Jobs\ProcessBackupsSync;
use App\Jobs\ProcessRepositorySync;
use App\Repository;
use Illuminate\Console\Command;

class SyncRepositoryCommand extends Command {

    use ExitableTrait;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:sync {id : The ID of the repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize a repository';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $repository = Repository::where('id', $this->argument('id'))->first();

        if (!$repository->exists) {
            return $this->exit(sprintf('<error>Repository with id %s not found.</error>', $this->argument('id')), 1);
        }

        ProcessRepositorySync::dispatch($repository);

        return $this->exit(sprintf('<info>Synchronization of repository with id %s started.</info>', $this->argument('id')), 0);
    }
}
