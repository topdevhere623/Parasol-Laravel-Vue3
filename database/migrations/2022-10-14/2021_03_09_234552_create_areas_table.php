<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id')
                ->index();
            $table->string('name');
            $table->enum('status', [
                'active',
                'inactive',
            ])
                ->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')
                ->references('id')
                ->on('cities');
        });
    }

    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
