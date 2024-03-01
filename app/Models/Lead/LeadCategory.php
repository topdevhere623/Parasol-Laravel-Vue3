<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use App\Models\Traits\Selectable;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadCategory extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;
    use Selectable;

    protected $guarded = ['id', 'uuid'];

    protected string $selectableValue = 'name';

    public function leadTags(): HasMany
    {
        return $this->hasMany(LeadTag::class);
    }
}
