<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberShippingDetail extends BaseModel
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            if (!$model->old_id) {
                $model->old_id = $model->id;
                $model->save();
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
