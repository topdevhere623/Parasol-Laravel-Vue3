<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use \App\Traits\EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->renameColumn('single_membership_price', 'single_membership_forecast_price');
            $table->renameColumn('family_membership_price', 'family_membership_forecast_price');
            $table->renameColumn('contract_expiry', 'current_contract_expiry');
            $table->renameColumn('slots', 'adult_slots');
            $table->renameColumn('kids_slots', 'kid_slots');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->after('single_membership_forecast_price', function (Blueprint $table) {
                $table->unsignedInteger('single_membership_price')
                    ->default(0);
                $table->integer('purchased_single_membership')
                    ->default(0);
            });

            $table->after('family_membership_forecast_price', function (Blueprint $table) {
                $table->unsignedInteger('family_membership_price')
                    ->default(0);
                $table->integer('purchased_family_membership')
                    ->default(0);
                $table->double('individual_kid_membership_price')
                    ->default(0);
                $table->integer('purchased_kid_membership')
                    ->default(0);
            });

            $table->timestamp('first_interaction')
                ->nullable()
                ->after('website');

            $table->timestamp('tranche_expiry')
                ->after('current_contract_expiry')
                ->nullable();

            $table->after('purchased_kid_membership', function (Blueprint $table) {
                $table->boolean('checkin_over_slots')
                    ->default(false);
                $table->boolean('display_slots_block')
                    ->default(false);
                $table->unsignedSmallInteger('auto_checkout_duration')
                    ->default(0);

                $table->double('adult_cost_per_visit')
                    ->default(0);
                $table->double('kid_cost_per_visit')
                    ->default(0);
                $table->double('contract_value')
                    ->default(0);
            });
        });

        Schema::table('partner_tranches', function (Blueprint $table) {
            $table->renameColumn('slots', 'adult_slots');
            $table->renameColumn('kids_slots', 'kid_slots');
        });

        \DB::table('partners')->update(['first_interaction' => \DB::raw('created_at')]);

        \App\Models\Partner\Partner::each(function ($partner) {
            $partner->auto_checkout_duration = $partner->clubs()->first()?->auto_checkout_after ?? 0;
            $partner->calculateSlots()->save();
        });

        $this->setEnumValues(
            'partner_tranches',
            'status',
            ['active', 'pending', 'awaiting_first_visit', 'inactive', 'expired'],
            false,
            'active'
        );
        $this->setEnumValues(
            'partner_contracts',
            'status',
            ['active', 'pending', 'inactive', 'expired'],
            false,
            'active'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('single_membership_forecast_price');
            $table->dropColumn('family_membership_forecast_price');
            $table->dropColumn('first_interaction');
            $table->dropColumn('pooled_access');
            $table->dropColumn('checkin_over_slots');
            $table->dropColumn('display_slots_block');
            $table->dropColumn('auto_checkout_duration');
            $table->dropColumn('adult_cost_per_visit');
            $table->dropColumn('kid_cost_per_visit');
        });

        Schema::table('partner_tranches', function (Blueprint $table) {
            $table->renameColumn('adult_slots', 'slots');
            $table->renameColumn('kid_slots', 'kids_slots');
        });
    }
};
