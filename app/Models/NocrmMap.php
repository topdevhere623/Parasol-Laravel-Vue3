<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class NocrmMap extends BaseModel
{
    use SoftDeletes;

    protected $table = 'nocrm_map';

    protected $guarded = ['id'];

    public function entityable()
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }

}
