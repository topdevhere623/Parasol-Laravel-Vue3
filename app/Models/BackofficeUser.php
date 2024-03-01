<?php

namespace App\Models;

use App\Casts\FileCast;
use App\Jobs\Plecto\PushBackofficeUserPlectoJob;
use App\Models\Club\Club;
use App\Models\Laratrust\Team;
use App\Models\Lead\CrmComment;
use App\Models\Traits\ActiveStatus;
use App\Models\Traits\ColumnLabelTrait;
use App\Models\Traits\ImageDataGettersTrait;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Kirschbaum\PowerJoins\PowerJoins;
use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Passport\HasApiTokens;
use ParasolCRM\Activities\ActivityTrait;

class BackofficeUser extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use PowerJoins;
    use SoftDeletes;
    use ImageDataGettersTrait;
    use ActivityTrait;
    use ColumnLabelTrait;
    use Selectable;
    use ActiveStatus;

    public const TEAM = Team::TEAM_IDS['adv_management'];

    public const FILE_CONFIG = [
        'avatar' => [
            'path' => 'admin/avatar',
            'size' => [200, 300, 500],
            'action' => ['resize'],
        ],
    ];

    public const STATUSES = [
        'inactive' => 'inactive',
        'active' => 'active',
    ];

    // Required for child classes
    protected $table = 'backoffice_users';

    public const PATH = 'uploads/';

    protected $fillable = [
        'name',
        'email',
        'password',
        'file',
        'avatar',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime:d F Y H:i',
        'avatar' => FileCast::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->team()->associate(static::TEAM);
        });
        static::saved(function (BackofficeUser $model) {
            if ($model->hasRole('sales') && $model->isDirty([
                'first_name',
                'last_name',
                'sales_units_target',
                'renewal_target_percent',
                'weekly_sales_units_target',
                'weekly_renewal_target_percent',
                'sales_amount_target',
                'weekly_sales_amount_target',
            ])) {
                PushBackofficeUserPlectoJob::dispatch($model);
            }
        });
    }

    // Relationships

    public function coupon(): MorphMany
    {
        return $this->morphMany(Coupon::class, 'couponable');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function passportLoginHistories(): MorphMany
    {
        return $this->morphMany(PassportLoginHistory::class, 'userable', 'user_type', 'user_id');
    }

    public function crmComments(): HasMany
    {
        return $this->hasMany(CrmComment::class);
    }

    public function hasTeam($teams): bool
    {
        $teams = is_array($teams) ? $teams : [$teams];
        foreach ($teams as $team) {
            if ($this->team_id == Team::TEAM_IDS[$team]) {
                return true;
            }
        }
        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasTeam('adv_management');
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

    public function scopeWhereHasTeam($query, $teamId)
    {
        return $query->whereHas('team', function ($query) use ($teamId) {
            $query->where('id', $teamId);
        });
    }

    public function getFullNameAttribute(): string
    {
        return $this->getRawOriginal('full_name') ?? "{$this->first_name} {$this->last_name}";
    }

    public function getPermissionsAttribute()
    {
        $userPermissions = $this->allPermissions();
        $permissions = [];
        foreach ($userPermissions as $permission) {
            $permissions[] = $permission->name;
        }
        return $permissions;
    }

    public function isHsbcManager(): bool
    {
        return $this->hasRole('hsbc_manager') || $this->hasRole('hsbc_manager_with_export');
    }

    public function findForPassport($identifier)
    {
        return $this->newQuery()
            ->where('email', $identifier)
            ->whereStatus(self::STATUSES['active'])
            ->first();
    }

    public function activityRules($value): array
    {
        return [
            'program_id' => fn () => optional(Program::find($value))->name,
            'club_id' => fn () => optional(Club::find($value))->title,
            'team_id' => fn () => optional(Team::find($value))->display_name,
            'password' => $value ? '*****' : '',
        ];
    }

    public static function getSelectable(): Collection
    {
        $query = static::query();

        $query
            ->selectRaw('CONCAT_WS(" ", first_name, last_name) as full_name, id')
            ->oldest('full_name')
            ->whereHasTeam(static::TEAM);

        return $query->pluck('full_name', 'id');
    }
}
