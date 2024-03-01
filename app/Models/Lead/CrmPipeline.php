<?php

namespace App\Models\Lead;

use App\Models\BaseModel;
use App\Models\Traits\Selectable;
use App\Traits\UuidOnCreating;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmPipeline extends BaseModel
{
    use SoftDeletes;
    use UuidOnCreating;
    use Selectable;

    // Properties

    /** @var array */
    protected $guarded = ['id'];

    protected $selectableValue = 'name';

    public function crmSteps(): HasMany
    {
        return $this->hasMany(CrmStep::class);
    }

    /**
     * Default id value for select filter
     */
    public static function getSelectableDefaultValue()
    {
        return '1';
    }
}
