<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipRenewal extends BaseModel
{
    use SoftDeletes;

    public const STATUSES = [
        'pending' => 'pending',
        'awaiting_due_date' => 'awaiting_due_date',
        'completed' => 'completed',
    ];

    protected $casts = [
        'end_date' => 'date',
        'due_date' => 'date',
        'is_30_days_email_sent' => 'boolean',
        'is_7_days_email_sent' => 'boolean',
        'is_expired_email_sent' => 'boolean',
        'is_7_days_expired_email_sent' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class, 'member_id');
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'old_plan_id')
            ->withTrashed();
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'new_plan_id')
            ->withTrashed();
    }

    public function renewalPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'renewal_package_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['completed']);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['pending']);
    }

    public function scopeAwaitingDueDate(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['awaiting_due_date']);
    }

    public function markAsAwaitingDueDate(): self
    {
        $this->status = self::STATUSES['awaiting_due_date'];
        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->status = self::STATUSES['completed'];
        return $this;
    }

    public function calculateDueDate(): Carbon
    {
        $date = $this->member?->end_date?->addDay();
        return $date && $date->isFuture() ? $date : today();
    }

    public function getRenewalUrlAttribute(): ?string
    {
        return route('booking.step-1', ['renewal' => $this->token]);
    }
}
