<?php

namespace App\Jobs;

use App\Repository;
use Cache;
use File;
use Log;
use olafnorge\borgphp\InfoCommand;
use RuntimeException;
use Str;
use Throwable;

class ProcessRepositorySync extends ProcessSync {

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
     * @var bool
     */
    protected $dropCache;

    /**
     * @var Repository
     */
    protected $repository;


    /**
     * Create a new job instance.
     *
     * @param Repository $repository
     * @param bool $dropCache
     */
    public function __construct(Repository $repository, bool $dropCache = false) {
        $this->repository = $repository;
        $this->dropCache = $dropCache;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle() {
        $lockKey = sprintf('%s::%s', class_basename($this->repository), $this->repository->id);
        $result = $this->lock($lockKey, config('borg.lock_ttl'))->get(function () {
            try {
                if ($this->dropCache) {
                    $this->repository->dropCache();
                }

                $borgIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());
                $bastionIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());
                $this->repository->stats = $this->getFromCache($borgIdRsaPath, $bastionIdRsaPath);
                $this->repository->saveOrFail();
                $this->chain([with(new ProcessBackupsSync($this->repository, $this->dropCache))])
                    ->delay(rand(0, 15))
                    ->allOnQueue('backups');
                $this->delete();
            } finally {
                File::exists($borgIdRsaPath) && File::delete($borgIdRsaPath);
                File::exists($bastionIdRsaPath) && File::delete($bastionIdRsaPath);
            }
        });

        if ($result === false) {
            Log::info(sprintf(
                'Releasing %s::%s at %s because it\'s locked. Attempts: %s',
                class_basename($this->repository),
                $this->repository->id,
                class_basename($this),
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
        return ['repository', sprintf('%s::%s', class_basename($this->repository), $this->repository->id)];
    }


    /**
     * @param string $borgIdRsaPath
     * @param string $bastionIdRsaPath
     * @return array
     */
    private function getFromCache(string $borgIdRsaPath, string $bastionIdRsaPath): array {
        return decrypt(Cache::store(config('borg.cache_store'))
            ->sear($this->repository->getCacheKey(), function () use ($borgIdRsaPath, $bastionIdRsaPath) {
                $arguments = [
                    $this->repository->repository,
                    config('borg.log_level'),
                    '--lock-wait', $this->getWaitForBorgLock(), // wait at most SECONDS for acquiring a repository/cache lock (default: 1)
                    '--rsh', value(function () use ($borgIdRsaPath, $bastionIdRsaPath): string {
                        $rsh = $this->repository->rsh;

                        if (Str::contains($rsh, '{% borg_id_rsa %}')) {
                            if (!File::put($borgIdRsaPath, $this->repository->borg_id_rsa, true) || !File::chmod($borgIdRsaPath, 0600)) {
                                $this->fail(new RuntimeException('Can not write private borg key'));
                            }

                            $rsh = Str::replaceFirst('{% borg_id_rsa %}', $borgIdRsaPath, $rsh);
                        }

                        if (Str::contains($rsh, '{% bastion_id_rsa %}')) {
                            if (!File::put($bastionIdRsaPath, $this->repository->bastion_id_rsa, true) || !File::chmod($bastionIdRsaPath, 0600)) {
                                $this->fail(new RuntimeException('Can not write private bastion key'));
                            }

                            $rsh = Str::replaceFirst('{% bastion_id_rsa %}', $bastionIdRsaPath, $rsh);
                        }

                        return $rsh;
                    }),
                ];

                return encrypt(array_get((array)with(new InfoCommand(
                    $arguments,
                    config('borg.storage_path'),
                    ['BORG_PASSPHRASE' => $this->repository->password]
                ))->mustRun()->getOutput(), 'cache.stats', []));
            }));
    }
}
