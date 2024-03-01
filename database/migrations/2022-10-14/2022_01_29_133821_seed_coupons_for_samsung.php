<?php

use App\Models\Coupon;
use Illuminate\Database\Migrations\Migration;

class SeedCouponsForSamsung extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 1; $i <= 100; $i++) {
            $coupon = new Coupon();
            $coupon->status = Coupon::STATUSES['active'];
            $coupon->code = Coupon::generateCode();
            $coupon->type = Coupon::TYPES['percentage'];
            $coupon->amount = 100;
            $coupon->owner = 'Viktoria';
            $coupon->note = 'corporate offer for SAMSUNG MEMBERS. One time complimentary visit , then upsel to membership.';
            $coupon->corporate_name = 'SAMSUNG MEMBERS';
            $coupon->usage_limit = 1;
            $coupon->expiry_date = \Carbon\Carbon::parse('01-02-2023');
            $coupon->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
