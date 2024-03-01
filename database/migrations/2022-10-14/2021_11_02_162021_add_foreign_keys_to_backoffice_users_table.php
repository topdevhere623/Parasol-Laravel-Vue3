<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBackofficeUsersTable extends Migration
{
    public function up()
    {
        Schema::table('backoffice_users', function (Blueprint $table) {
            $table->unsignedInteger('team_id')->change();
            $table->foreign('team_id')
                ->references('id')
                ->on('teams');

            $table->unsignedBigInteger('club_id')->change();
            $table->foreign('club_id')
                ->references('id')
                ->on('clubs');
        });
    }

    public function down()
    {
        Schema::table('backoffice_users', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropForeign(['team_id']);
        });
    }
}
