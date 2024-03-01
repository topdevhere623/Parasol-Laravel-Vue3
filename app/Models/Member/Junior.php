<?php

namespace App\Models\Member;

use App\Scopes\MemberTypeScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Junior extends Partner
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->casts = array_merge(parent::getCasts(), $this->casts);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new MemberTypeScope('junior'));

        static::creating(function ($model) {
            $model->member_type = static::MEMBER_TYPES['junior'];
        });
    }

    public static function generateMemberId($model): ?string
    {
        return $model->program->prefix.'3'.str_pad($model->parent_id, 5, '0', STR_PAD_LEFT)
            .(array_search($model->id, $model->member->juniors()->pluck('id')->toArray()) + 1);
    }
}
