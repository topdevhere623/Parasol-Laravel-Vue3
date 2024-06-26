<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')
                ->unique();
            $table->string('display_name')
                ->nullable();
            $table->string('description')
                ->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teams');
    }
}
