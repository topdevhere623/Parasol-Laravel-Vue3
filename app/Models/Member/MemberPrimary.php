<?php

namespace App\Models\Member;

use App\Scopes\MemberTypeScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;

class MemberPrimary extends Member
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;

    public const MAIN_EMAIL = [
        'personal_email' => 'personal_email',
        'recovery_email' => 'recovery_email',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new MemberTypeScope('member'));

        static::creating(function ($model) {
            $model->member_type = 'member';
        });
    }

    public static function generateMemberId($model): ?string
    {
        return $model->program->prefix.'1'.str_pad($model->id, 6, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class, 'parent_id')
            ->latestOfMany();
    }

    public function activePartner(): HasOne
    {
        return $this->partner()->active();
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'parent_id');
    }

    public function activePartners(): HasMany
    {
        return $this->partners()->active();
    }

    public function juniors(): HasMany
    {
        return $this->hasMany(Junior::class, 'parent_id');
    }

    public function activeJuniors(): HasMany
    {
        return $this->juniors()->active();
    }

    public function getLoginEmail(): ?string
    {
        return $this->main_email == self::MAIN_EMAIL['personal_email'] ? $this->email : $this->recovery_email;
    }
}
