<?php

namespace App\Models;

use App\Casts\JsonCast;
use App\Models\Traits\ColumnLabelTrait;
use App\Models\Traits\HasSelfParentTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasSelfParentTrait;
    use ColumnLabelTrait;

    public $timestamps = false;

    protected $casts = [
        'data' => JsonCast::class,
        'created_at' => 'datetime:d F Y H:i',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function userable()
    {
        return $this->morphTo('userable', 'user_type', 'user_id');
    }

    /**
     * Scope a query to only include by date.
     *
     * @param Builder $query
     */
    public function scopeFilterDate(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
