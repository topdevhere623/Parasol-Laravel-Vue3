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
        Schema::table('membership_renewals', function (Blueprint $table) {
            $table->after('old_plan_id', function (Blueprint $table) {
                $table->unsignedBigInteger('new_plan_id')
                    ->nullable()
                    ->index();
                $table->date('end_date')
                    ->nullable();
            });

            $table->foreign('old_plan_id')
                ->references('id')
                ->on('plans');

            $table->foreign('new_plan_id')
                ->references('id')
                ->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership_renewals', function (Blueprint $table) {
            $table->dropColumn(['new_plan_id', 'end_date']);
        });
    }
};
