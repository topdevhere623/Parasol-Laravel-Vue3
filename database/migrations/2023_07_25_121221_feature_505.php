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
            $table->after('billing_period', function (Blueprint $table) {
                $table->enum('access_type', ['prepaid', 'postpaid'])->default('prepaid');
                $table->unsignedFloat('single_membership_price')->default(0);
                $table->unsignedFloat('family_membership_price')->default(0);
                $table->unsignedFloat('individual_child_membership_price')->default(0);
                $table->unsignedInteger('classes_slots')->default(0);
                $table->unsignedInteger('family_membership_adults_per_slot')->default(0);
                $table->unsignedInteger('family_membership_child_slots')->default(0);

                $table->unsignedInteger('monthly_prepaid_checkin_slots_limit')->default(0);
                $table->unsignedFloat('monthly_over_limit_adult_fee')->default(0);
                $table->unsignedFloat('monthly_over_limit_child_fee')->default(0);

                $table->enum('kids_access_type', ['linked', 'individual'])->default('linked');
                $table->enum('slots_type', ['revolving', 'slots'])->default('revolving');
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
            $table->dropColumn([
                'access_type',
                'single_membership_price',
                'family_membership_price',
                'individual_child_membership_price',
                'classes_slots',
                'family_membership_adult_slots',
                'family_membership_child_slots',
                'monthly_prepaid_checkin_slots_limit',
                'monthly_over_limit_adult_fee',
                'monthly_over_limit_child_fee',
                'kids_access',
                'slots_type',
            ]);
        });
    }
};
