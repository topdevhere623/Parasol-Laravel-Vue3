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
        Schema::table('partner_payments', function (Blueprint $table) {
            $table->foreignId('partner_tranche_id')
                ->after('partner_id')
                ->index()
                ->nullable()
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
        Schema::table('partner_payments', function (Blueprint $table) {
            $table->dropColumn('partner_tranche_id');
        });
    }
};
