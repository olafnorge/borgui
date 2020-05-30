<?php

namespace App\Jobs;

use App\Backup;
use Cache;
use File;
use Log;
use olafnorge\borgphp\ListCommand;
use RuntimeException;
use Str;

class ProcessBrowseSync extends ProcessSync {

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

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
     * @var string
     */
    protected $folder;

    /**
     * @var string
     */
    protected $cacheKey;


    /**
     * Create a new job instance.
     *
     * @param Backup $backup
     * @param string $cacheKey
     * @param string $folder
     */
    public function __construct(Backup $backup, string $cacheKey, string $folder) {
        $this->backup = $backup;
        $this->folder = $folder;
        $this->cacheKey = $cacheKey;
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
                $borgIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());
                $bastionIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());

                Cache::tags($this->backup->getCacheTags())
                    ->sear($this->cacheKey, function () use ($borgIdRsaPath, $bastionIdRsaPath) {
                        $arguments = [
                            sprintf('%s::%s', $this->backup->repository->repository, $this->backup->name),
                            ltrim($this->folder, '/'),
                            '--pattern', sprintf('- %s/*/*', trim($this->folder, '/')),
                            '--json-lines',
                            config('borg.log_level'),
                            '--rsh', value(function () use ($borgIdRsaPath, $bastionIdRsaPath): string {
                                $rsh = $this->backup->repository->rsh;

                                if (Str::contains($rsh, '{% borg_id_rsa %}')) {
                                    if (!File::put($borgIdRsaPath, $this->backup->repository->borg_id_rsa, true) || !File::chmod($borgIdRsaPath, 0600)) {
                                        throw new RuntimeException('Can not write private borg key');
                                    }

                                    $rsh = Str::replaceFirst('{% borg_id_rsa %}', $borgIdRsaPath, $rsh);
                                }

                                if (Str::contains($rsh, '{% bastion_id_rsa %}')) {
                                    if (!File::put($bastionIdRsaPath, $this->backup->repository->bastion_id_rsa, true) || !File::chmod($bastionIdRsaPath, 0600)) {
                                        throw new RuntimeException('Can not write private bastion key');
                                    }

                                    $rsh = Str::replaceFirst('{% bastion_id_rsa %}', $bastionIdRsaPath, $rsh);
                                }

                                return $rsh;
                            }),
                        ];

                        /** @noinspection PhpParamsInspection */
                        return encrypt(collect(with(new ListCommand(
                            $arguments,
                            config('borg.storage_path'),
                            ['BORG_PASSPHRASE' => $this->backup->repository->password]
                        ))->mustRun()->getOutput())->sortBy('path')->all());
                    });
            } finally {
                File::exists($borgIdRsaPath) && File::delete($borgIdRsaPath);
                File::exists($bastionIdRsaPath) && File::delete($bastionIdRsaPath);
            }
        });

        // lock the process
        if ($result === false) {
            Log::info(sprintf(
                'Deleting %s::%s at %s for backup %s because it\'s locked.',
                class_basename($this->backup->repository),
                $this->backup->repository_id,
                class_basename($this),
                $this->backup->id
            ));

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            return $this->delete();
        }
    }


    /**
     * @return array
     */
    public function tags(): array {
        return ['browse', sprintf('%s::%s', class_basename($this->backup->repository), $this->backup->repository_id)];
    }
}
