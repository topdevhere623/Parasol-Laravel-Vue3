<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubTagTable extends Migration
{
    public function up()
    {
        Schema::create('club_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('club_tag_id');

            $table->foreign('club_id')
                ->references('id')
                ->on('clubs');

            $table->foreign('club_tag_id')
                ->references('id')
                ->on('club_tags');
        });
    }

    public function down()
    {
        Schema::dropIfExists('club_tag');
    }
}
