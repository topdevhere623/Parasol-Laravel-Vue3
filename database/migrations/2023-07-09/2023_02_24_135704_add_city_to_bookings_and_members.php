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
        Schema::table('bookings', function (Blueprint $table) {
            $table
                ->bigInteger('area_id')
                ->nullable()
                ->after('payment_method_id');
        });
        Schema::table('members', function (Blueprint $table) {
            $table
                ->bigInteger('area_id')
                ->nullable()
                ->after('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
};
