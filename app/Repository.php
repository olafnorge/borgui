<?php

namespace App;

use App\Jobs\ProcessRepositorySync;
use Cache;
use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * Class Repository
 *
 * @package App
 * @property int user_id
 * @property string name
 * @property string repository
 * @property string password
 * @property string rsh
 * @property string borg_id_rsa
 * @property string bastion_id_rsa
 * @property array stats
 * @property int total_chunks
 * @property int unique_chunks
 * @property int size
 * @property int compressed_size
 * @property int deduplicated_size
 * @property int unique_size
 * @property Backup|null last_backup
 * @property int backup_count
 */
class Repository extends Model {

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_chunks',
        'unique_chunks',
        'size',
        'compressed_size',
        'deduplicated_size',
        'unique_size',
        'last_backup',
        'backup_count',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'stats' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'repository',
        'password',
        'rsh',
        'borg_id_rsa',
        'bastion_id_rsa',
        'stats',
    ];


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot(): void {
        parent::boot();

        /**
         * Register a created model event with the dispatcher.
         *
         * @param \Closure|string $callback
         * @return void
         */
        self::created(function (Repository $model): void {
            Log::info(sprintf(
                'Adding %s::%s onto queue after it has been created.',
                class_basename($model),
                $model->id
            ));

            // do an initial sync
            ProcessRepositorySync::dispatch($model);
        });

        /**
         * Register an updated model event with the dispatcher.
         *
         * @param \Closure|string $callback
         * @return void
         */
        self::updated(function (Repository $model): void {
            if ($model->isDirty()) {
                Log::info(sprintf(
                    'Adding %s::%s onto queue after it has been updated.',
                    class_basename($model),
                    $model->id
                ));

                // sync changes
                ProcessRepositorySync::dispatch($model, true);
            }
        });

        /**
         * Register a deleted model event with the dispatcher.
         *
         * @param  \Closure|string  $callback
         * @return void
         */
        self::deleted(function (Repository $model): void {
            $model->dropCache();
        });
    }


    /**
     * @return string
     */
    public function getCacheKey(): string {
        return sprintf('borg_repository.%s', $this->id);
    }


    /**
     * Drops all of it's caches
     */
    public function dropCache(): void {
        Cache::store(config('borg.cache_store'))->forget($this->getCacheKey());
    }


    /**
     * Decrypt password on the fly
     *
     * @param $value
     * @return mixed|string
     */
    public function getPasswordAttribute($value) {
        return decrypt($value);
    }


    /**
     * Encrypt password on the fly
     *
     * @param $value
     */
    public function setPasswordAttribute($value) {
        $this->attributes['password'] = encrypt($value);
    }


    /**
     * Decrypt borg_id_rsa on the fly
     *
     * @param $value
     * @return mixed|string
     */
    public function getBorgIdRsaAttribute($value) {
        return decrypt($value);
    }


    /**
     * Encrypt borg_id_rsa on the fly
     *
     * @param $value
     */
    public function setBorgIdRsaAttribute($value) {
        $this->attributes['borg_id_rsa'] = encrypt($value);
    }


    /**
     * Decrypt bastion_id_rsa on the fly
     *
     * @param $value
     * @return mixed|string
     */
    public function getBastionIdRsaAttribute($value) {
        return decrypt($value);
    }


    /**
     * Encrypt bastion_id_rsa on the fly
     *
     * @param $value
     */
    public function setBastionIdRsaAttribute($value) {
        $this->attributes['bastion_id_rsa'] = encrypt($value);
    }


    /**
     * @return int
     */
    public function getTotalChunksAttribute(): int {
        return array_get($this->stats, 'total_chunks', 0);
    }


    /**
     * @return int
     */
    public function getUniqueChunksAttribute(): int {
        return array_get($this->stats, 'total_unique_chunks', 0);
    }


    /**
     * @return int
     */
    public function getSizeAttribute(): int {
        return array_get($this->stats, 'total_size', 0);
    }


    /**
     * @return int
     */
    public function getCompressedSizeAttribute(): int {
        return array_get($this->stats, 'total_csize', 0);
    }


    /**
     * @return int
     */
    public function getDeduplicatedSizeAttribute(): int {
        return array_get($this->stats, 'unique_csize', 0);
    }


    /**
     * @return int
     */
    public function getUniqueSizeAttribute(): int {
        return array_get($this->stats, 'unique_size', 0);
    }


    /**
     * @return Backup|null
     */
    public function getLastBackupAttribute(): ?Backup {
        return $this->backups()->latest('start')->first();
    }


    /**
     * @return int
     */
    public function getBackupCountAttribute(): int {
        return $this->backups()->count();
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function backups() {
        return $this->hasMany(Backup::class);
    }


    /**
     * @return array
     */
    public static function getRules(): array {
        return [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'repository' => 'required|string|max:4096',
            'password' => 'required|string|max:4096',
            'rsh' => 'nullable|string|max:4096',
            'borg_id_rsa' => 'nullable|string',
            'bastion_id_rsa' => 'nullable|string',
            'stats' => 'nullable|array',
        ];
    }
}
