<?php

use Illuminate\Database\Migrations\Migration;

class ChangeMembershipStatusInMembersTable extends Migration
{
    use \App\Traits\EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setEnumValues(
            'members',
            'membership_status',
            [
                'active',
                'expired',
                'cancelled',
                'redeemed',
                'processing',
                'transferred',
                'paused',
                'payment_defaulted_on_hold',
            ],
            false,
            'processing'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setEnumValues(
            'members',
            'membership_status',
            [
                'active',
                'expired',
                'cancelled',
                'processing',
                'transferred',
                'paused',
                'payment_defaulted_on_hold',
            ],
            false,
            'processing'
        );
    }
}
