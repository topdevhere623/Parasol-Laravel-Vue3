<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckinKidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkin_kids', function (Blueprint $table) {
            $table->unsignedBigInteger('kid_id');
            $table->unsignedBigInteger('checkin_id');

            $table->primary(['kid_id', 'checkin_id']);

            $table->foreign('kid_id')
                ->references('id')
                ->on('kids');

            //            $table->foreign('checkin_id')
            //                ->references('id')
            //                ->on('checkins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkin_kids');
    }
}
