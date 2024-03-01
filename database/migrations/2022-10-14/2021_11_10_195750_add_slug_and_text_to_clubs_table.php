<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugAndTextToClubsTable extends Migration
{
    public function up()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('slug')
                ->nullable()
                ->after('youtube');
            $table->text('text')
                ->nullable()
                ->after('slug');
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('text');
        });
    }
}
