<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ActiveStatus
{
    /**
     * Get only Active status row
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->getTable().'.status', 'active');
    }
}
