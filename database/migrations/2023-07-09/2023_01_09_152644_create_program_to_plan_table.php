<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_plan_referral', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id');
            $table
                ->foreign('program_id')
                ->references('id')
                ->on('programs');

            $table->unsignedBigInteger('plan_id');
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table
                ->enum('type', [
                    'exclude',
                    'include',
                ])
                ->default('exclude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_plan_referral');
    }
};
