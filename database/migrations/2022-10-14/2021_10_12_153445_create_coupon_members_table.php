<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponMembersTable extends Migration
{
    public function up()
    {
        Schema::create('coupon_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')
                ->nullable();
            $table->unsignedBigInteger('member_id');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons');

            $table->foreign('member_id')
                ->references('id')
                ->on('members');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_members');
    }
}
