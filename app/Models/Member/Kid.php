<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Booking;
use App\Models\Club\Checkin;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Kid extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'dob' => 'datetime:d F Y',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string)Str::orderedUuid();
        });

        static::created(function ($model) {
            if (!$model->old_id) {
                $model->old_id = $model->id;
            }

            if (!$model->member_id && optional($model->member)->program) {
                $model->member_id = $model->member->program->prefix.'4'.str_pad(
                    $model->parent_id,
                    5,
                    '0',
                    STR_PAD_LEFT
                ).$model->member->kids()->count();
            }
            $model->save();
        });
    }

    public function activityRules($value): array
    {
        return [
            'parent_id' => fn () => optional(Member::find($value))->full_name,
        ];
    }

    // Relationships

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class, 'parent_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function checkins(): BelongsToMany
    {
        return $this->belongsToMany(Checkin::class, 'checkin_kids');
    }

    public function activeCheckins(): BelongsToMany
    {
        return $this->checkins()->active();
    }

    public function getParents()
    {
        if (!$this->parent_id) {
            return [];
        }

        return Member::where('id', $this->parent_id)->orWhere('parent_id', $this->parent_id)->get();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute()
    {
        return $this->dob ? $this->dob->age : null;
    }
}
