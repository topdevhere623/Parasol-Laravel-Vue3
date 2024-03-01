<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRedeemedStatusesInCoupons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\Coupon::whereRaw('usage_limit = number_of_used')->where(
            'status',
            \App\Models\Coupon::STATUSES['inactive']
        )->chunkById(100, function ($coupons) {
            foreach ($coupons as $coupon) {
                $coupon->status = \App\Models\Coupon::STATUSES['redeemed'];
                $coupon->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            //
        });
    }
}
