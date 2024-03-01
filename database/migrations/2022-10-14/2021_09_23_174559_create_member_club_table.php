<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberClubTable extends Migration
{
    public function up()
    {
        Schema::create('member_club', function (Blueprint $table) {
            $table->integer('member_id')->unsigned();
            $table->integer('club_id')->unsigned();
            $table->primary(['member_id', 'club_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_club');
    }
}
