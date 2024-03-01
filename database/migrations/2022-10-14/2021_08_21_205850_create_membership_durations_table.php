<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembershipDurationsTable extends Migration
{
    public function up()
    {
        Schema::create('membership_durations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('color');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('membership_durations');
    }
}
