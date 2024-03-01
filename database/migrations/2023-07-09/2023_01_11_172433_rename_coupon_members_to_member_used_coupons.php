<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('coupon_members', 'member_used_coupons');

        \DB::table('permissions')
            ->whereRaw('name LIKE \'%CouponMember\'')
            ->update(['name' => \DB::raw("REPLACE(name, 'App\\\\Models\\\\CouponMember', 'App\\\\Models\\\\MemberUsedCoupon')")]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('member_used_coupons', 'coupon_members');

        \DB::table('permissions')
            ->whereRaw('name LIKE \'%MemberUsedCoupon\'')
            ->update(['name' => \DB::raw("REPLACE(name, 'App\\\\Models\\\\MemberUsedCoupon', 'App\\\\Models\\\\CouponMember')")]);
    }
};
