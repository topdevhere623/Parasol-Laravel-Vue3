<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilePathMapsTable extends Migration
{
    public function up()
    {
        Schema::create('file_path_maps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('old_path')->index();
            $table->string('new_path');
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_path_maps');
    }
}
