<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CouponPlan extends Pivot
{
    protected $table = 'coupon_plan';

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->where('coupon_id', $model->coupon_id)
                ->where('type', 'exclude')
                ->delete();
        });
    }
}
