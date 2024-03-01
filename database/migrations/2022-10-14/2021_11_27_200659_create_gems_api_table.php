<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGemsApiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gems_api', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')
                ->index();
            $table->foreignId('booking_id')
                ->index()
                ->nullable();
            $table->string('aff_id', 30)
                ->index();
            $table->string('token_id', 30)
                ->index();
            $table->string('loyal_id');
            $table->string('mem_adv_plus_id')
                ->index()
                ->nullable();
            $table->unsignedBigInteger('member_id')
                ->nullable()
                ->index();
            $table->string('first_name', 100)
                ->nullable();
            $table->string('last_name', 100)
                ->nullable();
            $table->dateTime('trn_datetime')
                ->nullable();
            $table->string('response_code', 30)
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gems_api');
    }
}
