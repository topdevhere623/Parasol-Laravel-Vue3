<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use App\Models\Traits\Selectable;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class LeadTag extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;
    use Selectable;

    public const DEFAULT_CATEGORY = 'Other';

    protected $table = 'lead_tags';

    protected $guarded = ['id', 'uuid'];

    protected string $selectableValue = 'name';

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function leadCategory(): HasMany
    {
        return $this->hasMany(LeadCategory::class);
    }

    public static function getOrCreate(array $tags): Collection
    {
        $defaultCategoryId = LeadCategory::where('name', static::DEFAULT_CATEGORY)->first()?->id;
        return collect($tags)->map(
            fn ($tag) => self::firstOrCreate(
                ['name' => $tag],
                ['lead_category_id' => $defaultCategoryId, 'name' => $tag]
            )
        );
    }
}
