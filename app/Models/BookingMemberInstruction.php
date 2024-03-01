<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingMemberInstruction extends BaseModel
{
    use SoftDeletes;

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function activityRules($value): array
    {
        return [
            'booking_id' => fn () => optional(Booking::find($value))->reference_id,
        ];
    }
}
