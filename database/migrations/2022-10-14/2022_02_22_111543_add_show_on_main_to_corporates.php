<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowOnMainToCorporates extends Migration
{
    public function up()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->boolean('show_on_main')
                ->default(false)
                ->after('title');
        });
    }

    public function down()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->dropColumn('show_on_main');
        });
    }
}
