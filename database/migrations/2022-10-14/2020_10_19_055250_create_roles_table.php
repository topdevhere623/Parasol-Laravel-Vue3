<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->unique();
            $table->string('display_name')
                ->nullable();
            $table->string('description')
                ->nullable();
            $table->unsignedInteger('team_id')
                ->nullable()
                ->index();
            $table->timestamps();

            $table->foreign('team_id')
                ->references('id')
                ->on('teams');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
