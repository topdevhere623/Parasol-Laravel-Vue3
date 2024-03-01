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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('duration', 15)
                ->nullable()
                ->change();

            $table->enum('duration_type', ['day', 'month', 'year', 'fixed_date'])
                ->default('month')
                ->after('duration_period');
        });

        \DB::table('plans')
            ->update(['duration_type' => \DB::raw('duration_period')]);

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('duration_period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table
                ->integer('duration')
                ->nullable()
                ->change();

            $table->enum('duration_period', ['day', 'month', 'year', 'fixed_date'])
                ->default('month')
                ->after('question_mark_description');

            $table->dropColumn('duration_type');
        });
    }
};
