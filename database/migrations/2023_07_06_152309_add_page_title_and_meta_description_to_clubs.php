<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('meta_title')
                ->after('airtable_id')
                ->nullable();
            $table->text('meta_description')
                ->after('meta_title')
                ->nullable();
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('meta_title');
            $table->dropColumn('meta_description');
        });
    }
};
