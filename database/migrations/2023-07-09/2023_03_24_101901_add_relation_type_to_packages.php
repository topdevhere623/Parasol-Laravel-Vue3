<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table
                ->enum('relation_type', ['corporate', 'reseller', 'b2c'])
                ->default('b2c')
                ->after('status');
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('relation_type');
        });
    }
};
