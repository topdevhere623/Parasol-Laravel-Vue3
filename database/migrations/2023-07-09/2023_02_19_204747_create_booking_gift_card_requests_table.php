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
        Schema::create('booking_gift_card_requests', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('gift_card_id')->constrained();
            $table->text('request')->nullable();
            $table->text('response')->nullable();
            $table->primary(['booking_id', 'gift_card_id'], 'primary_key_pair');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_used_gift_cards');
    }
};
