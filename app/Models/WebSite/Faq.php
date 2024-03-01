<?php

namespace App\Models\WebSite;

use App\Models\BaseModel;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'inactive' => 'Inactive',
        'active' => 'Active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function activityRules($value): array
    {
        return [
            'category_id' => fn () => optional(FaqCategory::find($value))->name,
        ];
    }
}
