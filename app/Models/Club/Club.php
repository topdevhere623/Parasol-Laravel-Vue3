<?php

namespace App\Models\Club;

use App\Casts\FileCast;
use App\Models\BaseModel;
use App\Models\City;
use App\Models\Gallery;
use App\Models\Member\Junior;
use App\Models\Member\Member;
use App\Models\Member\MemberClubPivot;
use App\Models\Member\MemberPrimary;
use App\Models\Offer;
use App\Models\Partner\Partner;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\Filterable;
use App\Models\Traits\Selectable;
use App\Scopes\ClubManagerScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Club extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;
    use Filterable;
    use Selectable;

    public const COVE_BEACH_ID = 83;

    public const STATUSES = [
        'active' => 'active',
        'inactive' => 'inactive',
        'cancelled' => 'cancelled',
        'paused' => 'paused',
        'in_progress' => 'in_progress',
    ];

    public const TRAFFICS = [
        'green' => 'green',
        'red' => 'red',
        'amber' => 'amber',
    ];

    public const ACCESS_TYPES = [
        'slots' => 'slots',
        'revolving' => 'revolving',
    ];

    public const FILE_CONFIG = [
        'home_photo' => [
            'path' => 'club/home-photo',
            'size' => [[548, 315], [548, 315], 1200],
            'action' => ['resize', 'png2jpg'],
        ],
        'club_photo' => [
            'path' => 'club/club-photo',
            'size' => [[593, 360], [593, 360], [1200, 683]],
            'action' => ['resize', 'png2jpg'],
        ],
        'checkout_photo' => [
            'path' => 'club/checkout-photo',
            'size' => [100, 350, 400],
            'action' => ['resize', 'png2jpg'],
        ],
        'detailed_club_info' => [
            'path' => 'club/info',
            'size' => [],
            'action' => [],
        ],
        'logo' => [
            'path' => 'club/logo',
            'size' => [100, 350, 400],
            'action' => ['resize'],
        ],
        'gallery' => [
            'path' => 'club/gallery',
            'size' => [[593, 360], [593, 360], [1200, 683]],
            'action' => ['resize', 'png2jpg'],
        ],
    ];

    protected $table = 'clubs';

    protected $casts = [
        'is_visible_plan' => 'boolean',
        'is_visible_website' => 'boolean',
        'checkin_availability' => 'boolean',
        'checkin_over_slots' => 'boolean',
        'display_slots_block' => 'boolean',
        'is_always_red' => 'boolean',
        'home_photo' => FileCast::class,
        'club_photo' => FileCast::class,
        'checkout_photo' => FileCast::class,
        'logo' => FileCast::class,
        'detailed_club_info' => FileCast::class,
    ];
    protected string $selectableValue = 'title';
    protected string $selectableOrderColumn = 'title';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ClubManagerScope());
    }

    // Relations

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ClubTag::class, 'club_tag', 'club_id', 'club_tag_id');
    }

    public function gallery(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_club', 'plan_id', 'club_id');
    }

    public function activePlans(): BelongsToMany
    {
        return $this->plans()->active();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(MemberPrimary::class, 'member_club', 'club_id', 'member_id')
            ->using(MemberClubPivot::class);
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->members()->active();
    }

    public function memberFavorites(): BelongsToMany
    {
        return $this->belongsToMany(MemberPrimary::class, 'member_club_favorite', 'club_id', 'member_id');
    }

    //    public function partners(): BelongsToMany
    //    {
    //        return $this->belongsToMany(Partner::class, 'member_club');
    //    }
    //
    //    public function activePartners(): BelongsToMany
    //    {
    //        return $this->partners()->active();
    //    }

    public function juniors(): BelongsToMany
    {
        return $this->belongsToMany(Junior::class, 'member_club');
    }

    public function activeJuniors(): BelongsToMany
    {
        return $this->juniors()->active();
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_club');
    }

    public function activeOffers(): BelongsToMany
    {
        return $this->offers()->active();
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class, 'club_id');
    }

    // Local scopes

    public function scopeVisibleInWebsite(Builder $query): Builder
    {
        return $query->where('is_visible_website', 1);
    }

    public function scopeVisibleInPlan(Builder $query): Builder
    {
        return $query->where('is_visible_plan', 1);
    }

    public function scopeWebSite(Builder $query): Builder
    {
        return $query->visibleInWebsite()->active();
    }

    public function scopeSort(Builder $query, int $programId = Program::ADV_PLUS_ID): Builder
    {
        return $query
            ->join('programs_to_clubs_sort', 'clubs.id', 'programs_to_clubs_sort.club_id')
            ->where('programs_to_clubs_sort.program_id', $programId)
            ->orderBy('programs_to_clubs_sort.sort');
    }

    public function scopeCheckinAvailable(Builder $query): Builder
    {
        return $query->where('checkin_availability', true);
    }

    public function scopeBookingAvailable(Builder $query): Builder
    {
        return $query->checkinAvailable()->visibleInPlan();
    }

    public function hasKidSlots(): bool
    {
        return $this->getKidSlots() > 0;
    }

    public function hasClassesSlots(): bool
    {
        return $this->partner->classes_slots > 0;
    }

    public function getAvailableAdultSlotsAttribute(): int
    {
        $partner = $this->partner;

        if ($partner->is_pooled_access) {
            $query = Checkin::whereHas('club', function ($query) use ($partner) {
                $query->withoutGlobalScopes()->whereHas('partner', function ($query) use ($partner) {
                    $query->withoutGlobalScopes()->where('partner_id', $partner->id);
                });
            });
        } else {
            $query = $this->checkins();
        }

        $query->withoutGlobalScopes()
            ->where([
                ['checked_in_at', '>=', Carbon::today()],
                ['checked_in_at', '<=', Carbon::tomorrow()],
            ]);

        if ($partner->isSlotsTypeRevolving()) {
            $query->where('status', Checkin::STATUSES['checked_in']);
        } else {
            $query->whereIn('status', [Checkin::STATUSES['checked_in'], Checkin::STATUSES['checked_out']]);
        }

        $usedSlots = $this->getAdultSlots() - $query->count();

        return max($usedSlots, 0);
    }

    public function getAvailableClassesSlots(Member $member): int
    {
        if (!$this->hasClassesSlots()) {
            return 0;
        }

        $partner = $this->partner;

        if ($partner->is_pooled_access) {
            $query = Checkin::whereHas('club', function ($query) use ($partner) {
                $query->withoutGlobalScopes()->whereHas('partner', function ($query) use ($partner) {
                    $query->withoutGlobalScopes()->where('partner_id', $partner->id);
                });
            });
        } else {
            $query = $this->checkins();
        }

        $query->withoutGlobalScopes()
            ->withoutTrashed()
            ->where([
                ['checked_in_at', '>=', today()->startOfWeek()->startOfDay()],
                ['checked_in_at', '<=', today()->endOfWeek()->endOfDay()],
                ['member_id', '=', $member->id],
                ['type', '=', Checkin::TYPES['class']],
            ]);

        $query->whereIn('status', [Checkin::STATUSES['checked_in'], Checkin::STATUSES['checked_out']]);

        $usedSlots = $partner->classes_slots - $query->count();

        return max($usedSlots, 0);
    }

    public function getAvailableKidSlotsAttribute(): int
    {
        $query = $this->checkins()
            ->where([
                ['checked_in_at', '>=', Carbon::today()],
                ['checked_in_at', '<=', Carbon::tomorrow()],
            ]);

        $partner = $this->partner;

        if ($partner->isSlotsTypeRevolving()) {
            $query->where('status', Checkin::STATUSES['checked_in']);
        } else {
            $query->whereIn('status', [Checkin::STATUSES['checked_in'], Checkin::STATUSES['checked_out']]);
        }

        $remainingSlots = $partner->kid_slots - $query->sum('number_of_kids');

        return max($remainingSlots, 0);
    }

    public function getAvailableAdultSlotsPercentAttribute(): int
    {
        return $this->getAdultSlots() ? (int)round($this->available_adult_slots / ($this->getAdultSlots() / 100)) : 0;
    }

    public function getAdultSlots(): int
    {
        return match ($this->partner->is_pooled_access) {
            true => $this->partner->adult_slots,
            false => $this->adult_slots,
        };
    }

    public function getKidSlots(): int
    {
        return match ($this->partner->is_pooled_access) {
            true => $this->partner->kid_slots,
            false => $this->kid_slots,
        };
    }

    protected function trafficIsAvailable(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['traffic'] != static::TRAFFICS['red'],
        );
    }

    public function updateTrafficBySlots(): self
    {
        // Zero Gravity | Dubai is red on some days
        if ($this->id == 33 && in_array(date('N'), [4, 5, 6])) {
            $this->traffic = Club::TRAFFICS['red'];

            return $this;
        }

        $this->traffic = match (true) {
            $this->available_adult_slots_percent == 0 || $this->is_always_red => self::TRAFFICS['red'],
            $this->available_adult_slots_percent <= 25 => self::TRAFFICS['amber'],
            default => self::TRAFFICS['green'],
        };

        return $this;
    }

    public function activityRules($value): array
    {
        return [
            'is_visible_plan' => fn () => $value ? 'Yes' : 'No',
            'is_visible_website' => fn () => $value ? 'Yes' : 'No',
            'checkin_availability' => fn () => $value ? 'Yes' : 'No',
            'checkin_over_slots' => fn () => $value ? 'Yes' : 'No',
            'display_slots_block' => fn () => $value ? 'Yes' : 'No',
            'is_always_red' => fn () => $value ? 'Yes' : 'No',
            'city_id' => fn () => optional(City::find($value))->name,
        ];
    }
}
