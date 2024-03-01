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
        Schema::create('partner_inventory_files', function (Blueprint $table) {
            $table->id();
            $table->string('file', 100);
            $table->string('original_name', 100)->nullable();
            $table->foreignId('partner_inventory_id')
                ->index()
                ->constrained();
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
        Schema::dropIfExists('partner_inventory_files');
    }
};
