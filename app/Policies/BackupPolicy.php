<?php

namespace App\Policies;

use App\User;
use App\Backup;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackupPolicy {

    use HandlesAuthorization;


    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user) {
        return $user->exists && !$user->disabled;
    }


    /**
     * Determine whether the user can view the backup.
     *
     * @param \App\User $user
     * @param \App\Backup $backup
     * @return mixed
     */
    public function view(User $user, Backup $backup) {
        return $user->id === $backup->repository->user_id;
    }


    /**
     * Determine whether the user can create backups.
     *
     * @param \App\User $user
     * @return mixed
     */
    public function create(User $user) {
        return true;
    }


    /**
     * Determine whether the user can update the backup.
     *
     * @param \App\User $user
     * @param \App\Backup $backup
     * @return mixed
     */
    public function update(User $user, Backup $backup) {
        return $user->id === $backup->repository->user_id;
    }


    /**
     * Determine whether the user can delete the backup.
     *
     * @param \App\User $user
     * @param \App\Backup $backup
     * @return mixed
     */
    public function delete(User $user, Backup $backup) {
        return $user->id === $backup->repository->user_id;
    }


    /**
     * Determine whether the user can restore the backup.
     *
     * @param \App\User $user
     * @param \App\Backup $backup
     * @return mixed
     */
    public function restore(User $user, Backup $backup) {
        return $user->id === $backup->repository->user_id;
    }


    /**
     * Determine whether the user can permanently delete the backup.
     *
     * @param \App\User $user
     * @param \App\Backup $backup
     * @return mixed
     */
    public function forceDelete(User $user, Backup $backup) {
        return $user->id === $backup->repository->user_id;
    }
}
