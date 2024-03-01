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
        Schema::table('crm_histories', function (Blueprint $table) {
            $table->unsignedInteger('activity_id')->nullable()->after('action_item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_histories', function (Blueprint $table) {
            $table->dropColumn('activity_id');
        });
    }
};
