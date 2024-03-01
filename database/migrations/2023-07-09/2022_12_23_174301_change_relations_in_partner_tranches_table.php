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
        Schema::table('partner_tranches', function (Blueprint $table) {
            $table->foreignId('partner_contract_id')
                ->after('partner_id')
                ->index()
                ->nullable()
                ->constrained();

            $table->dropForeign('partner_tranches_partner_id_foreign');
            $table->dropForeign('partner_tranches_partner_payment_id_foreign');

            $table->dropColumn('partner_id', 'partner_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_tranches', function (Blueprint $table) {
            $table->foreignId('partner_id')
                ->index()
                ->constrained();
            $table->foreignId('partner_payment_id')
                ->index()
                ->nullable()
                ->constrained();
        });
    }
};
