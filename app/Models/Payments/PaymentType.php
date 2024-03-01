<?php

namespace App\Models\Payments;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    public const NAME_ID = [
        'membership' => 1,
        'membership_renewal' => 2,
        'trial' => 3,
        'recurring' => 15,
        'card_change' => 20,
    ];
}
