<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmStep extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;

    public const DEFAULT_B2C_STEP = 3;

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    public function crmPipeline(): BelongsTo
    {
        return $this->belongsTo(CrmPipeline::class);
    }

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }
}
