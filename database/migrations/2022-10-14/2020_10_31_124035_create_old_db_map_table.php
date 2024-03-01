<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOldDbMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('old_db_map', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('new_id')
                ->index('new_id');
            $table->integer(
                'old_id'
            )->index('old_id');
            $table->string('type', 25)
                ->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('old_db_map');
    }
}
