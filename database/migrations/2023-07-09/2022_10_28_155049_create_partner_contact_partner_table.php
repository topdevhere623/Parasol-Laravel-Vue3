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
        Schema::create('partner_partner_contact', function (Blueprint $table) {
            $table->foreignId('partner_id')->constrained();
            $table->foreignId('partner_contact_id')->constrained();
            $table->primary(['partner_id', 'partner_contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_partner_contact');
    }
};
