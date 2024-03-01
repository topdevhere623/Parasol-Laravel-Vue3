<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanClubTable extends Migration
{
    public function up()
    {
        Schema::create('plan_club', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('club_id');
            $table->enum('type', [
                'exclude',
                'include',
                'fixed',
            ])
                ->default('exclude');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table->foreign('club_id')
                ->references('id')
                ->on('clubs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_club');
    }
}
