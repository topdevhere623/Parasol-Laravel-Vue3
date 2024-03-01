<?php

namespace App\Models\WebSite;

use App\Models\BaseModel;
use App\Models\Traits\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaqCategory extends BaseModel
{
    use SoftDeletes;
    use ActiveStatus;

    public const STATUSES = [
        'inactive' => 'Inactive',
        'active' => 'Active',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id');
    }

    public function activeFaqs(): HasMany
    {
        return $this->faqs()->active();
    }

    public function scopeSort(Builder $query): Builder
    {
        return $query->orderBy('sort');
    }
}
