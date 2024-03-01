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
        Schema::create('partner_tranche_partner_payment', function (Blueprint $table) {
            $table->foreignId('partner_tranche_id')->constrained();
            $table->foreignId('partner_payment_id')->constrained();
            $table->primary(['partner_tranche_id', 'partner_payment_id'], 'primary_key_pair');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_tranche_partner_payment');
    }
};
