<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingMemberInstructions extends Migration
{
    public function up()
    {
        Schema::create('booking_member_instructions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->unsignedBigInteger('booking_id');

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_member_instructions');
    }
}
