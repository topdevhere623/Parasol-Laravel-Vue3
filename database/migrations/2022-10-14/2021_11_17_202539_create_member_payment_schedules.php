<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPaymentSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_payment_schedules', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id')
                ->index();
            $table->unsignedBigInteger('member_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('plan_id')
                ->index();
            $table->unsignedDouble('monthly_amount');
            $table->unsignedDouble('first_payment_amount');
            $table->integer('first_number_of_days');
            $table->date('charge_date');
            $table->string('card_token', 70);
            $table->string('card_last4_digits', 4);
            $table->date('card_expiry_date');
            $table->string('card_scheme', 20);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings');

            $table->foreign('member_id')
                ->references('id')
                ->on('members');
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_payment_schedules');
    }
}
