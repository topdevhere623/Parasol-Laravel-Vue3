<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNationalitiesTable extends Migration
{
    public function up()
    {
        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)
                ->unique()
                ->index();
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nationalities');
    }
}
