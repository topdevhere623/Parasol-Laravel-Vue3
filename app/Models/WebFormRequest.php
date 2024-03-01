<?php

namespace App\Models;

use App\Models\Lead\Lead;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebFormRequest extends BaseModel
{
    use SoftDeletes;

    public const STATUSES = [
        'incoming' => 'incoming',
        'assigned' => 'assigned',
        'responded' => 'responded',
        'pending' => 'pending',
        'joined' => 'joined',
        'lost' => 'lost',
    ];

    protected $casts = [
        'data' => 'json',
        'is_entertainer' => 'bool',
    ];

    protected $guarded = ['id'];

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class, 'backoffice_user_id');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
