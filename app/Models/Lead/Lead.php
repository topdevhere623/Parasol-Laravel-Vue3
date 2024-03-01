<?php

namespace App\Models\Lead;

use App\Models\BackofficeUser;
use App\Models\BaseModel;
use App\Models\Booking;
use App\Models\Member\MemberPrimary;
use App\Models\Traits\ColumnLabelTrait;
use App\Models\Traits\Selectable;
use App\Scopes\SalesLeadScope;
use App\Traits\UuidOnCreating;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Lead extends BaseModel
{
    use SoftDeletes;
    use ColumnLabelTrait;
    use UuidOnCreating;
    use Selectable;

    public const CLOSED_STATUSES = [
        'won' => 'won',
        'cancelled' => 'cancelled',
        'lost' => 'lost',
    ];

    public const STATUSES = [
        'todo' => 'todo',
        'standby' => 'standby',
        ...self::CLOSED_STATUSES,
    ];

    public const OWNERS = [
        'Ritesh' => 84,
        'Olga' => 81,
        'Kunal' => 90,
    ];

    public const DEFAULT_OWNER = self::OWNERS['Olga'];

    public const OWNER_CACHE_KEY = 'lead_assigned_user_id';

    protected $table = 'leads';

    protected $casts = [
        'closed_at' => 'datetime',
        'remind_date' => 'date',
        'remind_time' => 'date:H:i:s',
        'reminder_at' => 'datetime',
    ];

    protected $guarded = ['id', 'uuid'];

    protected static function booted()
    {
        static::addGlobalScope(new SalesLeadScope());
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strlen(\Str::onlyNumbers($value)) >= 9 ? \Str::onlyNumbers($value) : null,
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => !empty($value) ? trim($value) : null,
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => trim(
                ($attributes['first_name'] ?? '').' '.($attributes['last_name'] ?? null)
            ),
        );
    }

    protected function title(): Attribute
    {
        $postfix = $this->booking?->member->member_id ?? $this->booking?->reference_id;

        return Attribute::make(
            get: fn ($value) => !empty($value) ? trim($value) : $this->full_name.($postfix ? ' ('.$postfix.')' : '')
        );
    }

    protected function reminderTimeAgo(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                if (in_array($attributes['status'], self::CLOSED_STATUSES)) {
                    return;
                }

                if ($attributes['remind_date']) {
                    $reminderDate = Carbon::parse($attributes['remind_date'])->setTimeFromTimeString(
                        $attributes['remind_time'] ?? '00:00'
                    );
                    $todayDate = now();

                    if (!$attributes['remind_time']) {
                        $reminderDate->startOfDay();
                        $todayDate->startOfDay();
                    }

                    if ($reminderDate->eq($todayDate)) {
                        return 'Today';
                    } elseif ($reminderDate->gt($todayDate)) {
                        return 'In '.$reminderDate->diffForHumans($todayDate, CarbonInterface::DIFF_ABSOLUTE, true);
                    }
                    return $reminderDate->diffForHumans($todayDate, CarbonInterface::DIFF_ABSOLUTE, true).' late';
                }
            }
        );
    }

    public function backofficeUser(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class);
    }

    public function member(): HasOne
    {
        return $this->hasOne(MemberPrimary::class);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class)->latestOfMany('step');
    }

    public function leadTags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class);
    }

    public function crmStep(): BelongsTo
    {
        return $this->belongsTo(CrmStep::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class, 'created_by');
    }

    public function crmComments(): MorphMany
    {
        return $this->morphMany(CrmComment::class, 'commentable')->sort();
    }

    public function crmHistory(): HasMany
    {
        return $this->hasMany(CrmHistory::class)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function reminderActivity(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class, 'reminder_activity_id', 'id');
    }

    public function reminderActivityLog(): BelongsTo
    {
        return $this->belongsTo(CrmComment::class, 'reminder_activity_log_id', 'id');
    }

    public function crmEmails(): HasMany
    {
        return $this->hasMany(CrmEmail::class);
    }

    public static function getSelectable(): Collection
    {
        $query = static::query();

        $query
            ->selectRaw(
                'CONCAT(leads.nocrm_id, " - ", leads.first_name, " ", COALESCE(leads.last_name, ""), " (", backoffice_users.first_name, " ", backoffice_users.last_name, ")") as text, leads.id'
            )
            ->leftJoin('backoffice_users', 'backoffice_users.id', '=', 'leads.backoffice_user_id')
            ->latest('leads.id');

        return $query->pluck('text', 'id');
    }

    public function setStep(string $stepName): self
    {
        $step = CrmStep::where([
            'name' => $stepName,
            'crm_pipeline_id' => $this->crmStep->crm_pipeline_id ?? 1,
        ])
            ->first();

        report_if(!$step, 'Unable to find CRM step: '.$stepName.' for lead: '.$this->id);

        if ($step) {
            $this->crmStep()->associate($step);
        }

        return $this;
    }

    public function scopeStandbyExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUSES['standby'])
            ->whereRaw(
                "
            CASE
                WHEN remind_time IS NOT NULL
                THEN DATE_FORMAT(CONCAT(remind_date, ' ', remind_time), '%Y-%m-%d %H:%i') <= '".date('Y-m-d H:i')."'
                ELSE remind_date <= '".date('Y-m-d')."'
            END
            "
            );
    }

    /**
     * Get options from constants with translation or default method
     *
     * @param string $key
     * @return array
     */
    public static function getConstOptions(string $key): array
    {
        $options = parent::getConstOptions($key);

        if ($key === 'statuses') {
            $options['todo'] = 'To do';
            $options['standby'] = 'Stand by';
        }

        return $options;
    }

    public static function randomOwnerId($tag = null, $updateCache = true)
    {
        $lastId = Cache::get(self::OWNER_CACHE_KEY."_{$tag}", 0);

        $excludeOwners = [3, 84, 168];

        $newId = BackofficeUser::whereRoleIs('sales')
            ->where('id', '>', $lastId)
            ->whereNotIn('id', $excludeOwners)
            ->active()
            ->min('id');

        if (!$newId && $lastId > 0) {
            $newId = BackofficeUser::whereRoleIs('sales')
                ->whereNotIn('id', $excludeOwners)
                ->active()
                ->min('id');
        }

        if ($updateCache) {
            Cache::put(self::OWNER_CACHE_KEY."_{$tag}", $newId, now()->addWeek());
        }

        return $newId ?? Lead::DEFAULT_OWNER;
    }
}
