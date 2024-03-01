<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class BackofficeUserSales extends BackofficeUser
{
    protected static function boot()
    {
        parent::boot();

        Relation::morphMap([
            BackofficeUser::class => BackofficeUserSales::class,
        ]);
    }

    public function scopeSales($query)
    {
        return $query->whereRoleIs('sales')
            ->orWhere('backoffice_users.id', 81);
    }

    public static function getSelectable(): Collection
    {
        $query = static::query();

        $query
            ->selectRaw('CONCAT_WS(" ", first_name, last_name) as full_name, id')
            ->oldest('full_name')
            ->whereRoleIs('sales')
            ->orWhere('id', 81);

        return $query->pluck('full_name', 'id');
    }
}
