<?php

namespace App\Models;

use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class HSBCBin extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    protected $table = 'hsbc_bins';

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    public const TYPES = [
        'credit' => 'credit',
        'debit' => 'debit',
        'test' => 'test',
    ];

    protected $casts = [
        'free_checkout' => 'boolean',
    ];

    public function scopeFreeCheckout($query): Builder
    {
        return $query->where($this->getTable().'.free_checkout', true);
    }
}
