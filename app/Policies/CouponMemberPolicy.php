<?php

namespace App\Policies;

use App\Models\MemberUsedCoupon;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class CouponMemberPolicy extends LaratrustModelsPolicy
{
    use HandlesAuthorization;

    protected ?string $model = MemberUsedCoupon::class;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Model $model
     * @return mixed
     */
    public function update(User $user, Model $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Model $model
     * @return mixed
     */
    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model log.
     *
     * @param User $user
     * @return mixed
     */
    public function log(User $user): bool
    {
        return false;
    }
}
