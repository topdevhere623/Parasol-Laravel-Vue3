<?php

namespace App\Models;

use App\Casts\FileCast;
use App\Models\Member\Member;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\HasMemberRelation;
use App\Models\Traits\Selectable;
use App\Models\WebSite\Page;
use App\Scopes\ProgramAdminScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;

class Program extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasApiTokens;
    use SoftDeletes;
    use ActiveStatus;
    use HasFactory;
    use Selectable;
    use HasMemberRelation;

    public const ADV_PLUS_ID = 2;
    public const ENTERTAINER_HSBC = 10;
    public const ENTERTAINER_SOLEIL_ID = 40;
    public const RAK_BANK_ID = 4;

    public const REFERRAL_PLAN_TYPES = [
        'exclude' => 'exclude',
        'include' => 'include',
    ];

    public const SOURCE_MAP = [
        'advplus' => 'web',
        'gems' => 'gr',
        'hsbc' => 'hs',
    ];

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    public const FILE_CONFIG = [
        'member_portal_logo' => [
            'path' => 'program/logo',
            'size' => [400],
            'action' => ['resize'],
        ],
        'website_logo' => [
            'path' => 'program/website-logo',
            'size' => [400],
            'action' => ['resize'],
        ],
        'referrals_page_img' => [
            'path' => 'program/referrals-page-image',
            'size' => [400],
            'action' => ['resize'],
        ],
    ];

    public const AMOUNT_TYPES = [
        'percentage' => 'percentage',
        'fixed' => 'fixed',
    ];

    public const DEFAULT_REFERRAL_CODE_TEMPLATE = '{'.Coupon::DEFAULT_CODE_LENGTH.'}';
    public const DEFAULT_REFERRAL_AMOUNT = 0;
    public const DEFAULT_REFERRAL_AMOUNT_TYPE = self::AMOUNT_TYPES['percentage'];
    public const DEFAULT_REFERRAL_PLAN_TYPES = [1, 2, 3];

    protected string $selectableValue = 'name';

    protected $fillable = [
        'name',
        'passkit_id',
        'prefix',
        'generate_passes',
        'email',
        'member_portal_logo',
        'member_portal_header_color',
        'color',
        'source',
        'password',
        'status',
        'last_seen',
        'color',
        'api_key',
        'has_access_api',
        'api_default_package_id',
        'landing_page_plan_id',
        'webhook_url',
        'referral_amount',
        'referral_amount_type',
        'club_document_available',
        'club_document_join_today_available',
        'club_document_main_page_package_id',
        'club_document_plan_id',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'member_portal_logo' => FileCast::class,
        'website_logo' => FileCast::class,
        'referrals_page_img' => FileCast::class,
        'last_seen' => 'datetime:d F Y H:i',
        'has_access_clubs' => 'boolean',
        'has_access_about_membership' => 'boolean',
        'has_access_profile' => 'boolean',
        'has_access_referrals' => 'boolean',
        'has_access_offers' => 'boolean',
        'has_visiting_family_membership' => 'boolean',
        'has_access_password_change' => 'boolean',
        'has_access_logout' => 'boolean',
        'passkit_button_on_top' => 'boolean',
        'has_access_visiting_family_membership' => 'boolean',
        'has_access_contact_us' => 'boolean',
        'has_access_all_clubs' => 'boolean',
        'has_access_api' => 'boolean',
        'club_document_available' => 'boolean',
        'club_document_join_today_available' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ProgramAdminScope());
    }

    // Relationships

    public function clubDocumentMainPagePackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'club_document_main_page_package_id');
    }

    public function clubDocumentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'club_document_plan_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function landingPagePlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function activePackages(): HasMany
    {
        return $this->packages()->active();
    }

    public function apiDefaultPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function programApiRequest(): HasMany
    {
        return $this->hasMany(ProgramApiRequest::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function passportLoginHistories(): MorphMany
    {
        return $this->morphMany(PassportLoginHistory::class, 'userable', 'user_type', 'user_id');
    }

    public function scopeHasAccessApi(Builder $query): Builder
    {
        return $query->where('has_access_api', true);
    }

    /**
     * Plans set for attaching to new coupon
     */
    public function referralPlans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'program_plan_referral')->using(ProgramPlanReferralPivot::class);
    }

    public function excludedPlans(): BelongsToMany
    {
        return $this->referralPlans()
            ->wherePivot('type', 'exclude');
    }

    public function includedPlans(): BelongsToMany
    {
        return $this->referralPlans()
            ->wherePivot('type', 'include');
    }

    public function api_logout()
    {
        $this->token()->revoke();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $this->token()->id)
            ->update([
                'revoked' => true,
            ]);
    }

    /**
     * Checks program source by alias inside model
     */
    public function isProgramSource(...$programSource): bool
    {
        if (count($programSource) > 0) {
            foreach (is_array($programSource[0]) ? $programSource[0] : $programSource as $value) {
                if (isset(static::SOURCE_MAP[$value]) && $this->source == static::SOURCE_MAP[$value]) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getTermsAndConditionsUrl(): ?string
    {
        return $this->attributes['terms_and_conditions_url'] ?: Page::getProtectedPageUrl('terms-and-conditions');
    }

    public function getFaqPageUrl(): ?string
    {
        return $this->attributes['faq_page_url'] ?: route('faq.index');
    }

    public function getClubGuideUrl(): ?string
    {
        return $this->attributes['club_guide_url'] ?: route('detailed_club_info_doc');
    }

    public function getWhatsappUrl(): ?string
    {
        return $this->attributes['whatsapp_url'] ?: 'https://wa.link/hds8te';
    }

    public function getApiWebhookUrl(): ?string
    {
        return match (true) {
            !app()->isLocal() => $this->webhook_url,
            default => 'http://app:80'.route('program-api.webhook-test', [], false),
        };
    }

    public function getClubDocFileName(): ?string
    {
        return \Str::of(\Str::slugExtended($this->name))
            ->append('-club-details.pdf')
            ->toString();
    }

    public function getClubDocsPath(): ?string
    {
        return $this->club_document_available ? 'club-details'.DIRECTORY_SEPARATOR.$this->getClubDocFileName() : null;
    }

    public function getClubDocsLink(): ?string
    {
        $path = $this->getClubDocsPath();

        return $path ? \URL::uploads($path) : null;
    }
}
