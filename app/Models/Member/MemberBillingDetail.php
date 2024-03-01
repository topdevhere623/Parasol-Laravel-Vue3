<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Corporate;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberBillingDetail extends BaseModel
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function corporate(): BelongsTo
    {
        return $this->belongsTo(Corporate::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberPrimary::class);
    }
}
