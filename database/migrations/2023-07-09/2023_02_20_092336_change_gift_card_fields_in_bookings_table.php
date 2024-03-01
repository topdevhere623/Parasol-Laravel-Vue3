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
        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('gift_card_amount', 'gift_card_discount_amount');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('gift_card_id')
                ->after('membership_source_id')
                ->nullable()
                ->index()
                ->constrained();
            $table->unsignedFloat('gift_card_amount')
                ->after('gift_card_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('gift_card_id', 'gift_card_amount');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('gift_card_discount_amount', 'gift_card_amount');
        });
    }
};
