<?php

namespace App\Models\Laratrust;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Kirschbaum\PowerJoins\PowerJoins;
use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    use PowerJoins;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'display_name',
    ];

    public $guarded = [];

    protected static function booted()
    {
        static::saving(function (Role $role) {
            $role->name ??= Str::slug($role->display_name);
        });
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_role');
    }
}
