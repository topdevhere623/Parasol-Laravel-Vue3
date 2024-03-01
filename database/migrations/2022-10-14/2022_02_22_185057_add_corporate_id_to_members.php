<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorporateIdToMembers extends Migration
{
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedBigInteger('corporate_id')
                ->nullable()
                ->after('membership_source_id');

            $table->foreign('corporate_id')
                ->references('id')
                ->on('corporates');
        });
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['corporate_id']);
            $table->dropColumn(['corporate_id']);
        });
    }
}
