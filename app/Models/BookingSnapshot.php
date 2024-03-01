<?php

namespace App\Models;

class BookingSnapshot extends BaseModel
{
    public $incrementing = false;

    protected $primaryKey = 'booking_id';

    protected $table = 'booking_snapshot';

    protected $fillable = ['data', 'booking_id'];

    protected $casts = [
        'data' => 'json',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class)->withTrashed();
    }
}
