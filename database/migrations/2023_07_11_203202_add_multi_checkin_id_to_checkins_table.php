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
        Schema::table('checkins', function (Blueprint $table) {
            $table->after('created_by', function (Blueprint $table) {
                $table->unsignedBigInteger('multi_checkin_id')
                    ->index()
                    ->nullable();
                $table->unsignedBigInteger('plan_id')
                    ->index()
                    ->nullable();
                $table->unsignedBigInteger('package_id')
                    ->index()
                    ->nullable();
                $table->unsignedBigInteger('program_id')
                    ->index()
                    ->nullable();
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
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropColumn([
                'multi_checkin_id',
                'plan_id',
                'package_id',
                'program_id',
            ]);
        });
    }
};
