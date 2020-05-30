<?php

namespace App;

use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Backup
 *
 * @package App
 * @property int repository_id
 * @property string borg_id
 * @property string name
 * @property string|null comment
 * @property string hostname
 * @property string username
 * @property float duration
 * @property array limits
 * @property array stats
 * @property array paths
 * @property int compressed_size
 * @property int deduplicated_size
 * @property int original_size
 * @property int number_files
 * @property Carbon|null start
 * @property Carbon|null end
 */
class Backup extends Model {

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'compressed_size',
        'deduplicated_size',
        'original_size',
        'number_files',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start',
        'end',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'duration' => 'float',
        'limits' => 'array',
        'stats' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'repository_id',
        'borg_id',
        'name',
        'comment',
        'hostname',
        'username',
        'duration',
        'limits',
        'start',
        'end',
        'stats',
        'paths',
    ];


    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot(): void {
        parent::boot();

        /**
         * Register a deleted model event with the dispatcher.
         *
         * @param \Closure|string $callback
         * @return void
         */
        self::deleted(function (Backup $model): void {
            $model->dropCache();
            $model->dropBrowseCache();
        });
    }


    /**
     * @return string
     */
    public function getCacheKey(): string {
        return sprintf('borg_backup.%s', $this->id);
    }


    /**
     * @return array
     */
    public function getCacheTags(): array {
        return [sprintf('borg_backup.%s', $this->id)];
    }


    /**
     * Drops all of it's caches
     */
    public function dropCache(): void {
        Cache::store(config('borg.cache_store'))->forget($this->getCacheKey());
    }


    /**
     * Drops browse cache
     */
    public function dropBrowseCache(): void {
        Cache::tags($this->getCacheTags())->flush();
    }


    /**
     * @return int
     */
    public function getCompressedSizeAttribute(): int {
        return array_get($this->stats, 'compressed_size', 0);
    }


    /**
     * @return int
     */
    public function getDeduplicatedSizeAttribute(): int {
        return array_get($this->stats, 'deduplicated_size', 0);
    }


    /**
     * @return int
     */
    public function getOriginalSizeAttribute(): int {
        return array_get($this->stats, 'original_size', 0);
    }


    /**
     * @return int
     */
    public function getNumberFilesAttribute(): int {
        return array_get($this->stats, 'nfiles', 0);
    }


    /**
     * Decrypt paths on the fly
     *
     * @param $value
     * @return mixed|string
     */
    public function getPathsAttribute($value) {
        return json_decode(decrypt($value));
    }


    /**
     * Encrypt password on the fly
     *
     * @param $value
     */
    public function setPathsAttribute($value) {
        $this->attributes['paths'] = encrypt(json_encode($value));
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repository() {
        return $this->belongsTo(Repository::class);
    }
}
