<?php

namespace App\Policies;

use App\Models\Booking;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class BookingPolicy extends LaratrustModelsPolicy
{
    use HandlesAuthorization;

    protected ?string $model = Booking::class;

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
}
