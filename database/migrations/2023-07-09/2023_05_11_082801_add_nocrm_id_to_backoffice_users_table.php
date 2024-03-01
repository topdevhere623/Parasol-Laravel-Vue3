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
        Schema::table('backoffice_users', function (Blueprint $table) {
            $table->string('nocrm_id', 15)
                ->nullable()
                ->after('program_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('backoffice_users', function (Blueprint $table) {
            $table->dropColumn('nocrm_id');
        });
    }
};
