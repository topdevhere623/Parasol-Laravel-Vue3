<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamRoleTable extends Migration
{
    public function up()
    {
        Schema::create('team_role', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedInteger('team_id');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Create a unique key
            $table->unique(['role_id', 'team_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_role');
    }
}
