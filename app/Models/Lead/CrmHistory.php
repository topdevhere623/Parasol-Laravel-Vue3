<?php

namespace App\Models\Lead;

use App\Casts\JsonCast;
use App\Enum\CRM\History\ActionTypeEnum;
use App\Models\BackofficeUser;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmHistory extends Model
{
    use UuidOnCreating;

    protected $guarded = ['id'];

    protected $casts = [
        'action_type' => ActionTypeEnum::class,
        'action_item' => JsonCast::class,
    ];

    /**
     * Get the parent historyable model (Lead, CrmComment, CrmStep).
     */
    public function historyable(): MorphTo
    {
        return $this->morphTo();
    }

    public function crmActivity(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class, 'activity_id');
    }

    public function backofficeUser(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class, 'user_id', 'id');
    }
}
