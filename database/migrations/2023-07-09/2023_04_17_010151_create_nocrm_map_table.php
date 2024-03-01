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
        Schema::create('nocrm_map', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('remote_id')
                ->index();
            $table->unsignedBigInteger('entity_id')
                ->index();
            $table->string('entity_type', 100)
                ->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nocrm_map');
    }
};
