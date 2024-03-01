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
        Schema::table('partner_tranches', function (Blueprint $table) {
            $table->after('family_membership_count', function (Blueprint $table) {
                $table->unsignedInteger('individual_kid_membership_count')->default(0);
            });
        });

        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->renameColumn('family_membership_adult_slots', 'family_membership_adults_per_slot');
            $table->renameColumn('family_membership_child_slots', 'family_membership_kids_per_slot');
            $table->renameColumn('individual_child_membership_price', 'individual_kid_membership_price');
            $table->renameColumn('monthly_over_limit_child_fee', 'monthly_over_limit_kid_fee');
        });

        Schema::table('partner_payments', function (Blueprint $table) {
            $table->renameColumn('date', 'date_forecasted');
            $table->date('date_actual')->nullable()->after('date');
        });

        \App\Models\Partner\PartnerTranche::chunk(
            100,
            fn ($tranches) => $tranches->each(fn ($tranche) => $tranche->save())
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_tranches', function (Blueprint $table) {
            $table->dropColumn('individual_kid_membership_count');
        });

        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->renameColumn('family_membership_adults_per_slot', 'family_membership_adult_slots');
            $table->renameColumn('family_membership_kids_per_slot', 'family_membership_child_slots');
            $table->renameColumn('individual_kid_membership_price', 'individual_child_membership_price');
            $table->renameColumn('monthly_over_limit_kid_fee', 'monthly_over_limit_child_fee');
        });

        Schema::table('partner_payments', function (Blueprint $table) {
            $table->renameColumn('date_forecasted', 'date');
            $table->dropColumn('date_actual');
        });
    }
};
