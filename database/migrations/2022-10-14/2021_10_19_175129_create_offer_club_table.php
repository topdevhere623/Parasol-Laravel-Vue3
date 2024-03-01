<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferClubTable extends Migration
{
    public function up()
    {
        Schema::create('offer_club', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('club_id');

            $table->foreign('offer_id')
                ->references('id')
                ->on('offers');
            $table->foreign('club_id')
                ->references('id')
                ->on('clubs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_club');
    }
}
