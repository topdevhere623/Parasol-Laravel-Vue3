<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponPlanTable extends Migration
{
    public function up()
    {
        Schema::create('coupon_plan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('plan_id');
            $table->enum('type', [
                'exclude',
                'include',
            ])
                ->default('exclude');

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_plan');
    }
}
