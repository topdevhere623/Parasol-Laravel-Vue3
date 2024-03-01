<?php

namespace App\Policies;

use App\Models\Lead\LeadDuplicate;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class LeadDuplicatePolicy extends LaratrustModelsPolicy
{
    use HandlesAuthorization;

    protected ?string $model = LeadDuplicate::class;

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
