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
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->unsignedDouble('third_party_commission_amount')
                ->after('monthly_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->dropColumn('third_party_commission_amount');
        });
    }
};
