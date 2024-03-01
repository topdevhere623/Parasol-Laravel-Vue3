<?php

namespace App\Models;

use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Traits\UuidOnCreating;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Passport\HasApiTokens;

class ProgramApiRequest extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasApiTokens;
    use SoftDeletes;
    use UuidOnCreating;

    protected $guarded = ['id'];

    protected $casts = [
        'request' => 'json',
        'booking_webhook_sent' => 'boolean',
    ];

    public const UPDATABLE_STATUSES = [
        Member::MEMBERSHIP_STATUSES['active'],
        Member::MEMBERSHIP_STATUSES['expired'],
        Member::MEMBERSHIP_STATUSES['cancelled'],
        Member::MEMBERSHIP_STATUSES['processing'],
        Member::MEMBERSHIP_STATUSES['payment_defaulted_on_hold'],
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class, 'member_id');
    }

    public function getRequestMemberData(): \stdClass
    {
        return (object)[
            'first_name' => $this->request['member']['first_name'] ?? null,
            'last_name' => $this->request['member']['last_name'] ?? null,
            'email' => $this->request['member']['email'] ?? null,
            'phone' => $this->request['member']['phone'] ?? null,
        ];
    }

    public function getRequestPartnerData(): \stdClass
    {
        return (object)[
            'first_name' => $this->request['partner']['first_name'] ?? null,
            'last_name' => $this->request['partner']['last_name'] ?? null,
            'email' => $this->request['partner']['email'] ?? null,
            'phone' => $this->request['partner']['phone'] ?? null,
        ];
    }
}
