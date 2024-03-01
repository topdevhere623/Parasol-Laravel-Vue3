<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('locatable_id');
            $table->string('locatable_type');
            $table->double('latitude');
            $table->double('longitude');

            $table->unsignedBigInteger('country_id')
                ->nullable();
            $table->unsignedBigInteger('city_id')
                ->nullable();
            $table->unsignedBigInteger('area_id')
                ->nullable();
            $table->string('street')
                ->nullable();
            $table->string('building_no')
                ->nullable();
            // END

            $table->string('phone')
                ->nullable();
            $table->string('email')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');

            $table->foreign('city_id')
                ->references('id')
                ->on('cities');

            $table->foreign('area_id')
                ->references('id')
                ->on('areas');

            $table->index(['locatable_id', 'locatable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
