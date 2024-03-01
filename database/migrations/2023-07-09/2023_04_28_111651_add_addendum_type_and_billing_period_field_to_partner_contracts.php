<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        DB::statement(
            "ALTER TABLE partner_contracts CHANGE COLUMN type type ENUM('first_year','renewal','addendum') NOT NULL DEFAULT 'first_year'"
        );
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table
                ->unsignedSmallInteger('billing_period')
                ->nullable()
                ->after('expiry_date');

            $table
                ->unsignedBigInteger('parent_id')
                ->nullable()
                ->after('type');
        });
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table
                ->foreign('parent_id')
                ->references('id')
                ->on('partner_contracts');
        });
    }

    public function down()
    {
        DB::statement(
            "ALTER TABLE partner_contracts CHANGE COLUMN type type ENUM('first_year','renewal') NOT NULL DEFAULT 'first_year'"
        );
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->dropColumn('billing_period');
            $table->dropColumn('parent_id');
        });
    }
};
