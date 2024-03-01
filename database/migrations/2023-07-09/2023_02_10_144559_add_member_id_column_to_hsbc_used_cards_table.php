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
        Schema::table('hsbc_used_cards', function (Blueprint $table) {
            $table->foreignId('member_id')
                ->nullable()
                ->after('booking_id')
                ->index()
                ->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hsbc_used_cards', function (Blueprint $table) {
            $table->dropColumn('member_id');
        });
    }
};
