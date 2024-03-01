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
            $table->renameColumn('units_target', 'sales_units_target');

            $table->unsignedInteger('renewal_target_percent')
                ->after('units_target')
                ->default(0);
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
            $table->renameColumn('sales_units_target', 'units_target');
            $table->dropColumn('renewal_target_percent');
        });
    }
};
