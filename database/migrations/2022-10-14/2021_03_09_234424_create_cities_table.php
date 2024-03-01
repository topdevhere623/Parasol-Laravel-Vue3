<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')
                ->index();
            $table->string('name');
            $table->enum('status', [
                'active',
                'inactive',
            ])
                ->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
