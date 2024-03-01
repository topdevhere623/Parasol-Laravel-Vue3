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
        Schema::table('programs', function (Blueprint $table) {
            $table
                ->boolean('club_document_available')
                ->after('coupon_template');

            $table
                ->boolean('club_document_join_today_available')
                ->after('club_document_available');

            $table
                ->unsignedBigInteger('club_document_main_page_package_id')
                ->nullable()
                ->after('club_document_join_today_available');

            $table
                ->unsignedBigInteger('club_document_plan_id')
                ->nullable()
                ->after('club_document_main_page_package_id');
        });
        Schema::table('programs', function (Blueprint $table) {
            $table
                ->foreign('club_document_main_page_package_id')
                ->references('id')
                ->on('packages');

            $table
                ->foreign('club_document_plan_id')
                ->references('id')
                ->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('club_document_available');
            $table->dropColumn('club_document_join_today_available');
            $table->dropColumn('club_document_main_page_package_id');
            $table->dropColumn('club_document_plan_id');
        });
    }
};
