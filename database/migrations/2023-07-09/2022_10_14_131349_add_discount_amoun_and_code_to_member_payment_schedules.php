<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->after('monthly_amount', function (Blueprint $table) {
                $table->double('monthly_discount_amount');
                $table->string('coupon_code', 70)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'monthly_discount_amount']);
        });
    }
};
