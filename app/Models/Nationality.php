<?php

namespace App\Models;

use App\Models\Member\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nationality extends BaseModel
{
    use HasFactory;

    /** @var bool */
    public $timestamps = false;

    /** @var array */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
