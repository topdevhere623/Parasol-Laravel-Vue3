<?php

namespace App\Models\Club;

use App\Models\BaseModel;
use App\Models\Member\Kid;
use App\Models\Member\Member;
use App\Models\Program;
use App\Scopes\ClubManagerScope;
use App\Scopes\HSBCProgramAdminScope;
use App\Scopes\ProgramAdminScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkin extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];

    public const STATUSES = [
        'checked_in' => 'checked_in',
        'checked_out' => 'checked_out',
        'paid_guest_fee' => 'paid_guest_fee',
        'turned_away' => 'turned_away',
        'turned_away_expired' => 'turned_away_expired',
    ];

    public const TYPES = [
        'regular' => 'regular',
        'class' => 'class',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    protected $hidden = ['pivot'];

    protected $table = 'checkins';

    protected static function booted()
    {
        static::addGlobalScope(new ClubManagerScope());
        static::addGlobalScope(new HSBCProgramAdminScope());
        static::addGlobalScope(new ProgramAdminScope());
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class)->withTrashed();
    }

    public function kids(): BelongsToMany
    {
        return $this->belongsToMany(Kid::class, 'checkin_kids');
    }

    public function program(): HasOneThrough
    {
        return $this->hasOneThrough(
            Program::class,
            Member::class,
            'id',
            'id',
            'member_id',
            'program_id'
        )->withTrashedParents();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['checked_in']);
    }

    public function checkout(): bool
    {
        if ($this->status == self::STATUSES['checked_in'] || $this->checked_out_at) {
            $this->checked_out_at = now();
            $this->status = self::STATUSES['checked_out'];
            return $this->save();
        }
        return true;
    }

    public function activityRules($value): array
    {
        return [
            'member_id' => fn () => optional(Member::find($value))->full_name,
            'club_id' => fn () => optional(Club::find($value))->title,
            'status' => fn () => ucfirst(str_replace('_', ' ', $value)),
        ];
    }
}
