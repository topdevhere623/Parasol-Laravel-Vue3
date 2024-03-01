<?php

namespace App\Models\Member;

use App\Scopes\MemberTypeScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Partner extends Member
{
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;

    // Scopes

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new MemberTypeScope('partner'));

        static::creating(function ($model) {
            $model->member_type = static::MEMBER_TYPES['partner'];
        });
    }

    public static function generateMemberId($model): ?string
    {
        return $model->program->prefix.'2'.str_pad($model->parent_id, 5, '0', STR_PAD_LEFT).'0';
    }

    public function activityRules($value): array
    {
        return [
            'parent_id' => fn () => optional(Member::find($value))->full_name,
        ];
    }

    public function getLoginEmail(): ?string
    {
        return $this->member->getLoginEmail() == $this->email ? null : $this->email;
    }
}
