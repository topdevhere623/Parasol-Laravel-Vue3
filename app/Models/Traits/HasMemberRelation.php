<?php

namespace App\Models\Traits;

use App\Models\Member\Junior;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Member\Partner;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasMemberRelation
{
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function primaryMembers(): HasMany
    {
        return $this->hasMany(MemberPrimary::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->active();
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }

    public function activePartners(): HasMany
    {
        return $this->partners()->active();
    }

    public function juniors(): HasMany
    {
        return $this->hasMany(Junior::class);
    }

    public function activeJuniors(): HasMany
    {
        return $this->juniors()->active();
    }
}
