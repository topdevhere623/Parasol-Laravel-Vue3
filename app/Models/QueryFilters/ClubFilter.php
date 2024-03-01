<?php

namespace App\Models\QueryFilters;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;

class ClubFilter extends QueryFilter
{
    protected $searchable = [
        'name',
    ];

    public function before(): Builder
    {
        return $this->builder
            ->with('city');
    }

    public function filterName(string $value): Builder
    {
        return $this->builder->where('title', 'like', "%{$value}%");
    }

    public function filterRecentlyVisited(string $value): Builder
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $this->builder
                ->addSelect(DB::raw('max(`checkins`.`checked_in_at`) as checked_in_max'))
                ->leftJoin(
                    'checkins',
                    function (QueryBuilder $join) {
                        $join->on('clubs.id', '=', 'checkins.club_id')
                            ->where('checkins.member_id', Auth::id());
                    }
                )
                ->where('checkins.member_id', Auth::id())
                ->groupBy('clubs.id')
                ->reorder(DB::raw('checked_in_max'), 'desc');
        }

        return $this->builder;
    }

    public function filterFavorites(string $value): Builder
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $this->builder->having('is_favorite', '>', 0);
        }
        return $this->builder;
    }
}
