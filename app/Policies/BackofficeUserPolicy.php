<?php

namespace App\Policies;

use App\Models\BackofficeUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackofficeUserPolicy extends LaratrustModelsPolicy
{
    use HandlesAuthorization;

    protected ?string $model = BackofficeUser::class;
}
