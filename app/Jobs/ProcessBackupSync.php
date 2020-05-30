<?php

namespace App\Jobs;

use App\Backup;
use Cache;
use Carbon\Carbon;
use File;
use Log;
use olafnorge\borgphp\CreateCommand;
use olafnorge\borgphp\InfoCommand;
use RuntimeException;
use Str;

class ProcessBackupSync extends ProcessSync {

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * @var Backup
     */
    protected $backup;

    /**
     * @var bool
     */
    protected $dropCache;


    /**
     * Create a new job instance.
     *
     * @param Backup $backup
     * @param bool $dropCache
     */
    public function __construct(Backup $backup, bool $dropCache = false) {
        $this->backup = $backup;
        $this->dropCache = $dropCache;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle() {
        $lockKey = sprintf('%s::%s', class_basename($this->backup->repository), $this->backup->repository_id);
        $result = $this->lock($lockKey, config('borg.lock_ttl'))->get(function () {
            try {
                if ($this->dropCache) {
                    $this->backup->dropCache();
                }

                $borgIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());
                $bastionIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());
                $archive = $this->getFromCache($borgIdRsaPath, $bastionIdRsaPath);
                $this->backup->fill([
                    'comment' => array_get($archive, 'comment'),
                    'hostname' => array_get($archive, 'hostname'),
                    'username' => array_get($archive, 'username'),
                    'duration' => array_get($archive, 'duration', 0.0),
                    'limits' => array_get($archive, 'limits', []),
                    'start' => Carbon::parse(array_get($archive, 'start', now()->toString())),
                    'end' => Carbon::parse(array_get($archive, 'end', now()->toString())),
                    'stats' => array_get($archive, 'stats', []),
                    'paths' => array_values(collect(CreateCommand::getPathsFromCommand(
                        array_get($archive, 'command_line'),
                        $this->backup->name
                    ))->sort()->toArray()),
                ])->saveOrFail();
                $this->delete();
            } finally {
                File::exists($borgIdRsaPath) && File::delete($borgIdRsaPath);
                File::exists($bastionIdRsaPath) && File::delete($bastionIdRsaPath);
            }
        });

        if ($result === false) {
            Log::info(sprintf(
                'Releasing %s::%s at %s for backup %s because it\'s locked. Attempts: %s',
                class_basename($this->backup->repository),
                $this->backup->repository_id,
                class_basename($this),
                $this->backup->id,
                $this->attempts()
            ));

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->release($this->attempts() * $this->getWaitForBorgLock());
        }
    }


    /**
     * @return array
     */
    public function tags(): array {
        return ['backup', sprintf('%s::%s', class_basename($this->backup->repository), $this->backup->repository_id)];
    }


    /**
     * @param string $borgIdRsaPath
     * @param string $bastionIdRsaPath
     * @return array
     */
    private function getFromCache(string $borgIdRsaPath, string $bastionIdRsaPath): array {
        return decrypt(Cache::store(config('borg.cache_store'))
            ->sear($this->backup->getCacheKey(), function () use ($borgIdRsaPath, $bastionIdRsaPath) {
                $arguments = [
                    sprintf('%s::%s', $this->backup->repository->repository, $this->backup->name),
                    config('borg.log_level'),
                    '--lock-wait', $this->getWaitForBorgLock(), // wait at most SECONDS for acquiring a repository/cache lock (default: 1)
                    '--rsh', value(function () use ($borgIdRsaPath, $bastionIdRsaPath): string {
                        $rsh = $this->backup->repository->rsh;

                        if (Str::contains($rsh, '{% borg_id_rsa %}')) {
                            if (!File::put($borgIdRsaPath, $this->backup->repository->borg_id_rsa, true) || !File::chmod($borgIdRsaPath, 0600)) {
                                $this->fail(new RuntimeException('Can not write private borg key'));
                            }

                            $rsh = Str::replaceFirst('{% borg_id_rsa %}', $borgIdRsaPath, $rsh);
                        }

                        if (Str::contains($rsh, '{% bastion_id_rsa %}')) {
                            if (!File::put($bastionIdRsaPath, $this->backup->repository->bastion_id_rsa, true) || !File::chmod($bastionIdRsaPath, 0600)) {
                                $this->fail(new RuntimeException('Can not write private bastion key'));
                            }

                            $rsh = Str::replaceFirst('{% bastion_id_rsa %}', $bastionIdRsaPath, $rsh);
                        }

                        return $rsh;
                    }),
                ];

                return encrypt(array_first(array_get((array)with(new InfoCommand(
                    $arguments,
                    config('borg.storage_path'),
                    ['BORG_PASSPHRASE' => $this->backup->repository->password]
                ))->mustRun()->getOutput(), 'archives', [])));
            }));
    }
}
