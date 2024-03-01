<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPaymentSchedulePayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_payment_schedule_payment', function (Blueprint $table) {
            $table->integer('member_payment_schedule_id')->unsigned();
            $table->integer('payment_id')->unsigned();
            $table->primary(['member_payment_schedule_id', 'payment_id'], 'primary_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_payment_schedule_payment');
    }
}
