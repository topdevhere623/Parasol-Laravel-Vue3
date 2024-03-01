<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use App\Scopes\CrmActivityStatusScope;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmActivity extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new CrmActivityStatusScope());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CrmActivity::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'parent_id');
    }

    public function crmComments(): HasMany
    {
        return $this->hasMany(CrmComment::class);
    }

    public function scopeSort($query): Builder
    {
        return $query->orderBy('position', 'asc');
    }
}
