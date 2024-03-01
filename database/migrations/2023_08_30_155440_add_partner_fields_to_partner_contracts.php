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
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->after('individual_kid_membership_price', function (Blueprint $table) {
                $table->unsignedFloat('adult_cost_per_visit')
                    ->default(0);
                $table->unsignedFloat('kid_cost_per_visit')
                    ->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->dropColumn('adult_cost_per_visit');
            $table->dropColumn('kid_cost_per_visit');
        });
    }
};
