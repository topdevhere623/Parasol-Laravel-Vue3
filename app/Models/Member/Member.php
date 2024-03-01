<?php

namespace App\Models\Member;

use App\Casts\FileCast;
use App\Models\Area;
use App\Models\BackofficeUser;
use App\Models\BaseModel;
use App\Models\Booking;
use App\Models\Club\Checkin;
use App\Models\Club\Club;
use App\Models\Corporate;
use App\Models\Coupon;
use App\Models\GemsApi;
use App\Models\Lead\Lead;
use App\Models\Package;
use App\Models\PassportLoginHistory;
use App\Models\Payments\Payment;
use App\Models\Plan;
use App\Models\Program;
use App\Models\ProgramApiRequest;
use App\Models\Referral;
use App\Models\Traits\Filterable;
use App\Models\Traits\HasSelfParentTrait;
use App\Models\Traits\Selectable;
use App\Observers\MemberObserver;
use App\Relations\HasOneWithSecondKey;
use App\Scopes\ProgramAdminScope;
use App\Traits\SecondKeyRelationTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Member extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasSelfParentTrait;
    use HasRelationships;
    use SecondKeyRelationTrait;
    use Selectable;
    use Filterable;

    // Constants
    public const MEMBERSHIP_STATUSES = [
        'active' => 'active',
        'expired' => 'expired',
        'cancelled' => 'cancelled',
        'redeemed' => 'redeemed',
        'processing' => 'processing',
        'transferred' => 'transferred',
        'paused' => 'paused',
        'payment_defaulted_on_hold' => 'payment_defaulted_on_hold',
    ];

    public const AVAILABLE_FOR_LOGIN_STATUSES = [
        self::MEMBERSHIP_STATUSES['active'],
        self::MEMBERSHIP_STATUSES['payment_defaulted_on_hold'],
        self::MEMBERSHIP_STATUSES['expired'],
        self::MEMBERSHIP_STATUSES['processing'],
    ];

    public const AVAILABLE_FOR_OWN_COUPON_STATUSES = [
        self::MEMBERSHIP_STATUSES['active'],
        self::MEMBERSHIP_STATUSES['payment_defaulted_on_hold'],
        self::MEMBERSHIP_STATUSES['processing'],
    ];

    public const MEMBER_TYPES = [
        'member' => 'member',
        'partner' => 'partner',
        'junior' => 'junior',
    ];

    public const LOGIN = [
        'personal_email' => 'Personal email',
        'recovery_email' => 'Recovery email',
    ];

    public const LOGIN_BADGES = [
        'personal_email' => 'green',
        'recovery_email' => 'gray',
    ];

    public const FILE_CONFIG = [
        'avatar' => [
            'path' => 'member/avatar',
            'size' => [200, 300, 500],
            'action' => ['resize', 'crop', 'png2jpg'],
        ],
    ];

    // Properties

    /** @var string */
    protected $table = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $hidden = ['pivot', 'deleted_at', 'password'];

    protected $casts = [
        'start_date' => 'date:d F Y',
        'end_date' => 'date:d F Y',
        'dob' => 'date:d F Y',
        'last_seen_at' => 'datetime:d F Y H:i',
        'password_created_at' => 'datetime:d F Y H:i',
        'avatar' => FileCast::class,
        'linkedin_verified' => 'boolean',
    ];

    protected $guard = 'api';

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        // This Observer not registered in EventServiceProvider because of extend by child models
        static::observe(MemberObserver::class);
        static::addGlobalScope(new ProgramAdminScope());
    }

    public function getEmailForPasswordReset()
    {
        return $this->login_email;
    }

    // Relationships

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function membershipSource(): BelongsTo
    {
        return $this->belongsTo(MembershipSource::class);
    }

    public function membershipDurations(): BelongsToMany
    {
        return $this->belongsToMany(
            MembershipDuration::class,
            'member_membership_duration',
            'member_id',
            'membership_duration_id'
        );
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function corporate(): BelongsTo
    {
        return $this->belongsTo(Corporate::class);
    }

    public function gemsApi(): HasOne
    {
        return $this->hasOne(GemsApi::class, 'member_id');
    }

    public function programApiRequest(): HasOne
    {
        return $this->hasOne(ProgramApiRequest::class, 'member_id');
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'member_club', 'member_id', 'club_id')
            ->using(MemberClubPivot::class);
    }

    public function checkinAvailableClubs(): BelongsToMany
    {
        return $this->clubs()->checkinAvailable();
    }

    public function favoriteClubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'member_club_favorite', 'member_id', 'club_id');
    }

    public function activeClubs(): BelongsToMany
    {
        return $this->clubs()->active();
    }

    public function activeUnusedClubs(): ?Collection
    {
        $myClubIds = $this
            ->clubs()
            ->pluck('id')
            ->toArray();

        return $this
            ->plan
            ->availableClubs()
            ->whereNotIn('id', $myClubIds)
            ->get();
    }

    public function referrals(): HasMany
    {
        return $this->hasManyWithSecondKey(Referral::class, 'member_id');
    }

    public function activeReferrals(): HasMany
    {
        return $this->referrals()->active();
    }

    public function coupon(): HasOneWithSecondKey
    {
        return $this
            ->hasOneWithSecondKey(Coupon::class, 'couponable_id')
            ->where('couponable_type', Coupon::COUPONABLE_TYPES['member']);
    }

    public function activeCoupon(): HasOne
    {
        return $this->coupon()->active();
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class, 'member_id');
    }

    public function activeCheckin(): HasOne
    {
        return $this->hasOne(Checkin::class, 'member_id')
            ->active()
            ->latestOfMany();
    }

    public function memberBillingDetail(): HasOne
    {
        return $this->hasOne(MemberBillingDetail::class, 'member_id')
            ->latestOfMany();
    }

    public function memberShippingDetail(): HasOne
    {
        return $this->hasOne(MemberShippingDetail::class, 'member_id')
            ->latestOfMany();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class, 'parent_id');
    }

    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class, 'parent_id')
            ->latestOfMany();
    }

    public function activePartner(): HasOne
    {
        return $this->partner()->active();
    }

    public function kids(): HasMany
    {
        return $this->hasManyWithSecondKey(Kid::class, 'parent_id');
    }

    public function juniors(): HasMany
    {
        return $this->hasMany(Junior::class, 'parent_id');
    }

    public function activeJuniors(): HasMany
    {
        return $this
            ->juniors()
            ->active();
    }

    public function passKit(): HasOne
    {
        return $this->hasOne(MemberPasskit::class, 'member_id', 'id');
    }

    public function passportLoginHistories(): MorphMany
    {
        return $this->morphMany(PassportLoginHistory::class, 'userable', 'user_type', 'user_id');
    }

    public function memberPaymentSchedules(): HasMany
    {
        return $this->hasMany(MemberPaymentSchedule::class, 'member_id');
    }

    public function memberPaymentSchedule(): HasOne
    {
        return $this->hasOne(MemberPaymentSchedule::class, 'member_id')
            ->latestOfMany();
    }

    public function memberActivePaymentSchedule(): HasOne
    {
        return $this->memberPaymentSchedule()
            ->active();
    }

    public function memberPortalPaymentSchedule(): HasOne
    {
        return $this->memberPaymentSchedule()
            ->cardChangeable();
    }

    public function membershipRenewals(): HasMany
    {
        return $this->hasManyWithSecondKey(MembershipRenewal::class, 'member_id');
    }

    public function pendingMembershipRenewal(): HasOne
    {
        return $this->hasOne(MembershipRenewal::class, 'member_id')
            ->pending()
            ->latestOfMany();
    }

    public function awaitingDueDateMembershipRenewal(): HasOne
    {
        return $this->hasOne(MembershipRenewal::class, 'member_id')
            ->awaitingDueDate()
            ->latestOfMany();
    }

    public function bdmBackofficeUser(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class, 'bdm_backoffice_user_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Scopes

    /**
     * Get only active()
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('membership_status', static::MEMBERSHIP_STATUSES['active']);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('membership_status', static::MEMBERSHIP_STATUSES['expired']);
    }

    public function scopeBySource(Builder $query, $value): Builder
    {
        return $query->where('source', $value);
    }

    public function scopeByProgramId(Builder $query, $value): Builder
    {
        return $query->where('program_id', $value);
    }

    public function scopeAvailableForMonthlyCharge(Builder $query): Builder
    {
        return $query->whereIn('membership_status', $this->availableForMonthlyChargeStatuses);
    }

    public function availableForLoginScope(Builder $query): Builder
    {
        return $query->whereIn('membership_status', self::AVAILABLE_FOR_LOGIN_STATUSES);
    }

    // Accessors and Mutators

    public function setFailedPaymentStatus(): static
    {
        $this->membership_status = Member::MEMBERSHIP_STATUSES['payment_defaulted_on_hold'];

        return $this;
    }

    /**
     * Get the member's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the member's full name with member ID.
     */
    public function getMemberShortDataAttribute(): string
    {
        return "{$this->full_name} - Member ID: {$this->member_id}";
    }

    /**
     * Get the member's full name, member ID, email and phone.
     */
    public function getMemberFullDataAttribute(): string
    {
        return "{$this->full_name} {$this->member_id} {$this->email} {$this->phone}";
    }

    public function getAvailableForMonthlyChargeStatusesAttribute(): array
    {
        return [
            self::MEMBERSHIP_STATUSES['active'],
            self::MEMBERSHIP_STATUSES['payment_defaulted_on_hold'],
        ];
    }

    public function getLocationAttribute(): null|string
    {
        return trim($this->area?->name.', '.$this->area?->city?->name, ' ,');
    }

    public function getPrimaryMemberId(): ?int
    {
        return $this->member_type === static::MEMBER_TYPES['member'] ? $this->id : $this->parent_id;
    }

    public function getAdminUrlAttribute(): ?string
    {
        switch ($this->member_type) {
            case self::MEMBER_TYPES['member']:
                return "/member-primary/{$this->id}";
            case self::MEMBER_TYPES['partner']:
                return "/member-primary/{$this->parent_id}/member-partner/{$this->id}";
            case self::MEMBER_TYPES['junior']:
                return "/member-primary/{$this->parent_id}/junior/{$this->id}";

            default:
                return '';
        }
    }

    public function api_logout(): void
    {
        $this->token()->revoke();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $this->token()->id)
            ->update([
                'revoked' => true,
            ]);
    }

    public function findForPassport($identifier): ?self
    {
        return $this->newQuery()
            ->where('login_email', $identifier)
            ->whereIn(
                'membership_status',
                self::AVAILABLE_FOR_LOGIN_STATUSES
            )
            ->orderByRaw('membership_status, member_type, id')
            ->first();
    }

    public function isAvailableForLogin(): bool
    {
        return in_array($this->membership_status, self::AVAILABLE_FOR_LOGIN_STATUSES);
    }

    public function isAvailableForOwnCoupon(): bool
    {
        return $this->member_type === static::MEMBER_TYPES['member']
            && in_array($this->membership_status, self::AVAILABLE_FOR_OWN_COUPON_STATUSES)
            && $this->program->has_access_referrals;
    }

    public function activityRules($value): array
    {
        return [
            'program_id' => fn () => optional(Program::find($value))->name,
            'package_id' => fn () => optional(Package::find($value))->title,
            'plan_id' => fn () => optional(Plan::find($value))->title,
            'membership_source_id' => fn () => optional(MembershipSource::find($value))->title,
            'password' => $value ? '*****' : '',
        ];
    }

    public function isExpired(): bool
    {
        return $this->membership_status == self::MEMBERSHIP_STATUSES['expired'];
    }

    public function isActive(): bool
    {
        return $this->membership_status == self::MEMBERSHIP_STATUSES['active'];
    }

    public function isAvailableForMonthlyCharge(): bool
    {
        return in_array($this->membership_status, $this->availableForMonthlyChargeStatuses);
    }

    // Checks: can be checked-in (doesn't check clubs)
    public function canCheckin(): bool
    {
        return $this->isActive();
    }

    public function hasPasskitAccess(): bool
    {
        return $this->canCheckin() && optional($this->program)->generate_passes;
    }

    public function getLoginEmail(): ?string
    {
        return $this->email;
    }

    public static function getSelectable(): \Illuminate\Support\Collection
    {
        $model = new static();
        $query = $model->getQuery();

        $query->selectRaw('CONCAT_WS(" ", first_name, last_name) as full_name, id');

        $query->oldest('full_name');

        return $query->pluck('full_name', 'id');
    }

    public function hasAccess(string $module): bool
    {
        $additionalCondition = true;

        if ($module == 'referrals') {
            $additionalCondition = $this->isActive();
        }

        return $this->program->{'has_access_'.$module} && $additionalCondition;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'member_id', 'id');
    }
}
