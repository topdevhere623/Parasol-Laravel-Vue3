<?php

namespace App\Models\Lead;

use App\Casts\JsonCast;
use App\Models\BackofficeUser;
use App\Models\BaseModel;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmEmail extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    protected $casts = [
        'threaded_content' => JsonCast::class,
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function crmComment(): BelongsTo
    {
        return $this->belongsTo(CrmComment::class);
    }

    public function backofficeUser(): BelongsTo
    {
        return $this->belongsTo(BackofficeUser::class);
    }

    public function crmAttachments(): MorphMany
    {
        return $this->morphMany(CrmAttachment::class, 'attachable');
    }
}
