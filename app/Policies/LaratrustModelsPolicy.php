<?php

namespace App\Policies;

use ErrorException;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class LaratrustModelsPolicy
{
    use HandlesAuthorization;

    protected ?string $model = null;

    public function __construct()
    {
        throw_unless(!is_null($this->model), ErrorException::class, 'Model is missed');
    }

    /**
     * Determine whether the user can view the index.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return mixed
     */
    public function index(User $user): bool
    {
        return $user->hasPermission('index-'.$this->model);
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-'.$this->model);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return mixed
     */
    public function view(User $user, Model $model): bool
    {
        return $user->hasPermission('view-'.$this->model);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create-'.$this->model);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return mixed
     */
    public function update(User $user, Model $model): bool
    {
        return $user->hasPermission('update-'.$this->model);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User  $user
     * @param Model $model
     *
     * @return mixed
     */
    public function delete(User $user, Model $model): bool
    {
        return $user->hasPermission('delete-'.$this->model);
    }

    /**
     * Determine whether the user can view the model log.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function log(User $user): bool
    {
        return $user->hasPermission('log-'.$this->model);
    }

    /**
     * Determine whether the user can export csv.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('export-'.$this->model);
    }
}
