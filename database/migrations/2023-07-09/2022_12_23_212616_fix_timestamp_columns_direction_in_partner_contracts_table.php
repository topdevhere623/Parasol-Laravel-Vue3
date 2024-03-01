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
        Schema::table('partner_contracts', function ($table) {
            $table->renameColumn('created_at', 'created_at2');
            $table->renameColumn('updated_at', 'updated_at2');
        });

        Schema::table('partner_contracts', function ($table) {
            $table->after('expiry_date', function (Blueprint $table) {
                $table->timestamps();
            });
        });

        // Copy the data across to the new column:
        DB::table('partner_contracts')->update([
            'created_at' => DB::raw('created_at2'),
            'updated_at' => DB::raw('updated_at2'),
        ]);

        // Remove the old column:
        Schema::table('partner_contracts', function (Blueprint $table) {
            $table->dropColumn('created_at2', 'updated_at2');
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
            //
        });
    }
};
