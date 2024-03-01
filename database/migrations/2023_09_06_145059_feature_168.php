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
        Schema::table('partners', function (Blueprint $table) {
            $table->enum('slots_type', ['revolving', 'slots'])
                ->after('contract_value')
                ->default('revolving');

            $table->unsignedInteger('classes_slots')
                ->default(0)
                ->after('kid_slots');

            $table->date('tranche_expiry')
                ->nullable()
                ->change();

            $table->date('first_interaction')
                ->nullable()
                ->change();
        });

        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->unsignedInteger('single_membership_kids_per_slot')
                ->default(0)
                ->after('classes_slots');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->renameColumn('daily_slots', 'adult_slots');
            $table->renameColumn('kids_slots', 'kid_slots');
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->enum('type', ['regular', 'class'])
                ->after('status')
                ->default('regular');
        });

        \App\Models\Partner\Partner::each(function ($partner) {
            $partner->calculateSlots()->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('slots_type');
        });

        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->dropColumn('single_membership_kids_per_slot');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->renameColumn('adult_slots', 'daily_slots');
            $table->renameColumn('kid_slots', 'kids_slots');
        });
    }
};
