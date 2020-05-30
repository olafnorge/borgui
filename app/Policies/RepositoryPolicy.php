<?php

namespace App\Policies;

use App\User;
use App\Repository;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepositoryPolicy {

    use HandlesAuthorization;


    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user) {
        return $user->exists && !$user->disabled;
    }


    /**
     * Determine whether the user can view the repository.
     *
     * @param \App\User $user
     * @param \App\Repository $repository
     * @return mixed
     */
    public function view(User $user, Repository $repository) {
        return $user->id === $repository->user_id;
    }


    /**
     * Determine whether the user can create repositories.
     *
     * @param \App\User $user
     * @return mixed
     */
    public function create(User $user) {
        return (bool)$user->id;
    }


    /**
     * Determine whether the user can update the repository.
     *
     * @param \App\User $user
     * @param \App\Repository $repository
     * @return mixed
     */
    public function update(User $user, Repository $repository) {
        return $user->id === $repository->user_id;
    }


    /**
     * Determine whether the user can delete the repository.
     *
     * @param \App\User $user
     * @param \App\Repository $repository
     * @return mixed
     */
    public function delete(User $user, Repository $repository) {
        return $user->id === $repository->user_id;
    }


    /**
     * Determine whether the user can restore the repository.
     *
     * @param \App\User $user
     * @param \App\Repository $repository
     * @return mixed
     */
    public function restore(User $user, Repository $repository) {
        return $user->id === $repository->user_id;
    }


    /**
     * Determine whether the user can permanently delete the repository.
     *
     * @param \App\User $user
     * @param \App\Repository $repository
     * @return mixed
     */
    public function forceDelete(User $user, Repository $repository) {
        return $user->id === $repository->user_id;
    }
}
