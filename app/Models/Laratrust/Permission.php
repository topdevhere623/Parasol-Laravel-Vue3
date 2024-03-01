<?php

namespace App\Models\Laratrust;

use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    public $guarded = [];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}
