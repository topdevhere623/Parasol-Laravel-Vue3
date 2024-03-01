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
        Schema::table('web_form_requests', function (Blueprint $table) {
            $table->boolean('is_entertainer')
                ->default(false)
                ->after('backoffice_user_id');
            $table->foreignId('lead_id')
                ->after('is_entertainer')
                ->nullable()
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_form_requests', function (Blueprint $table) {
            $table->dropColumn('is_entertainer');
            $table->dropColumn('lead_id');
        });
    }
};
