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
            $table->after('sales_units_target', function (Blueprint $table) {
                $table->unsignedInteger('weekly_sales_units_target')
                    ->default(0);
                $table->unsignedDouble('sales_revenue_target')
                    ->default(0);
                $table->unsignedDouble('weekly_sales_revenue_target')
                    ->default(0);
            });
            $table->unsignedInteger('weekly_renewal_target_percent')
                ->after('renewal_target_percent')
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
            $table->dropColumn('weekly_sales_units_target');
            $table->dropColumn('weekly_renewal_target_percent');
            $table->dropColumn('sales_amount_target');
            $table->dropColumn('weekly_sales_amount_target');
        });
    }
};
