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
        Schema::create('partner_contract_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_contract_id')
                ->index()
                ->constrained();
            $table->string('file', 100);
            $table->string('original_name', 100)
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_contract_files');
    }
};
