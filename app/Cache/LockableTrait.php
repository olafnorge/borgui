<?php
namespace App\Cache;

use Cache;
use Illuminate\Cache\RedisLock;

trait LockableTrait {


    /**
     * @var RedisLock
     */
    protected $lock;


    /**
     * @param $name
     * @param int $seconds
     * @return RedisLock
     */
    protected function lock($name, $seconds = 0): RedisLock {
        $this->lock = Cache::lock($name, $seconds);

        return $this->lock;
    }


    /**
     * @param callable $callback
     * @return bool
     */
    protected function get(callable $callback): bool {
        if ($this->lock->acquire()) {
            try {
                $result = $this->lock->get($callback);

                return $result === null ? true : $result;
            } finally {
                $this->lock->release();
            }
        }

        return false;
    }
}
