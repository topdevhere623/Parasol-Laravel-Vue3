<?php

namespace App\Models\Member;

use App\Casts\FileCast;
use App\Models\BaseModel;
use App\Models\Traits\HasMemberRelation;
use App\Models\WebFormRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipProcess extends BaseModel
{
    use SoftDeletes;
    use HasMemberRelation;

    public const STATUSES = [
        'pending' => 'pending',
        'complete' => 'complete',
        'cancelled' => 'cancelled',
        'overdue' => 'overdue',
    ];

    public const FILE_CONFIG = [
        'file' => [
            'path' => 'member/membership-process',
            'size' => [100, 500],
            'action' => ['resize'],
        ],
    ];

    protected $casts = [
        'action_due_date' => 'date:d F Y',
        'file' => FileCast::class,
    ];

    protected $guarded = ['id'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function webFormRequest(): BelongsTo
    {
        return $this->belongsTo(WebFormRequest::class);
    }
}
