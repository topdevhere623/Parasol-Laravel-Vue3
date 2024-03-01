<?php

use App\Traits\EnumChangeTrait;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusesInCoupons extends Migration
{
    use EnumChangeTrait;

    public function up()
    {
        $this->setEnumValues(
            'coupons',
            'status',
            [
                'inactive',
                'active',
                'expired',
                'redeemed',
            ],
            false,
            'inactive'
        );
    }

    public function down()
    {
        $this->setEnumValues(
            'coupons',
            'status',
            [
                'inactive',
                'active',
                'expired',
            ],
            false,
            'inactive'
        );
    }
}
