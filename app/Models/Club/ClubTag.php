<?php

namespace App\Models\Club;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubTag extends BaseModel
{
    use SoftDeletes;

    public $guarded = ['id'];

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_tag');
    }
}
