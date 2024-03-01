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
            $table->unsignedDouble('plan_third_party_commission_amount')
                ->after('plan_amount');
            $table->unsignedDouble('extra_child_third_party_commission_amount')
                ->after('extra_child_amount');
            $table->unsignedDouble('extra_junior_third_party_commission_amount')
                ->after('extra_junior_amount');
            $table->unsignedDouble('total_third_party_commission_amount')
                ->after('total_price');
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
            $table->dropColumn('subtotal_third_party_commission_amount');
            $table->dropColumn('extra_child_third_party_commission_amount');
            $table->dropColumn('extra_junior_third_party_commission_amount');
            $table->dropColumn('total_third_party_commission_amount');
        });
    }
};
