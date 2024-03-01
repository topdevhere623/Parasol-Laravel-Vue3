<?php

namespace App\Observers;

use App\Models\Coupon;

class CouponObserver
{
    public function saving(Coupon $model)
    {
        if ($model->status == Coupon::STATUSES['active'] && $model->number_of_used >= $model->usage_limit) {
            $model->status = Coupon::STATUSES['redeemed'];
        }
        $model->email_domain = strtolower(implode(', ', array_map('trim', explode(',', $model->email_domain))));
    }
}
