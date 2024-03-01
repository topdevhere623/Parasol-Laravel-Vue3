<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHSBCUsedCardsTable extends Migration
{
    public function up()
    {
        Schema::create('hsbc_used_cards', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('payment_id')
                ->index();
            $table->unsignedBigInteger('booking_id')
                ->index();
            $table->unsignedBigInteger('package_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('plan_id')
                ->index();
            $table->unsignedDouble('total_price')
                ->nullable();
            $table->string('card_token', 70);
            $table->integer('bin');
            $table->integer('card_last4_digits');
            $table->string('card_scheme', 20)->nullable();
            $table->date('card_expiry_date');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');
            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings');
            $table->foreign('package_id')
                ->references('id')
                ->on('packages');
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hsbc_used_cards');
    }
}
